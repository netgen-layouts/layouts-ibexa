<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Validator;

use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section as IbexaSection;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Netgen\Layouts\Ibexa\Validator\Constraint\Section;
use Netgen\Layouts\Ibexa\Validator\SectionValidator;
use Netgen\Layouts\Tests\TestCase\ValidatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[CoversClass(SectionValidator::class)]
final class SectionValidatorTest extends ValidatorTestCase
{
    private MockObject&Repository $repositoryMock;

    private MockObject&SectionService $sectionServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new Section();
    }

    /**
     * @param string[] $allowedSections
     */
    #[DataProvider('validateDataProvider')]
    public function testValidate(string $identifier, array $allowedSections, bool $isValid): void
    {
        $this->sectionServiceMock
            ->expects(self::once())
            ->method('loadSectionByIdentifier')
            ->with(self::identicalTo($identifier))
            ->willReturn(
                new IbexaSection(
                    [
                        'identifier' => $identifier,
                    ],
                ),
            );

        $this->constraint->allowedSections = $allowedSections;
        $this->assertValid($isValid, $identifier);
    }

    public function testValidateNull(): void
    {
        $this->sectionServiceMock
            ->expects(self::never())
            ->method('loadSectionByIdentifier');

        $this->assertValid(true, null);
    }

    public function testValidateInvalid(): void
    {
        $this->sectionServiceMock
            ->expects(self::once())
            ->method('loadSectionByIdentifier')
            ->with(self::identicalTo('unknown'))
            ->willThrowException(new NotFoundException('section', 'unknown'));

        $this->assertValid(false, 'unknown');
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\Layouts\Ibexa\Validator\Constraint\Section", "Symfony\Component\Validator\Constraints\NotBlank" given');

        $this->constraint = new NotBlank();
        $this->assertValid(true, 'value');
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessageMatches('/^Expected argument of type "string", "int(eger)?" given$/');

        $this->assertValid(true, 42);
    }

    public static function validateDataProvider(): iterable
    {
        return [
            ['media', [], true],
            ['media', ['media'], true],
            ['media', ['standard'], false],
            ['media', ['media', 'standard'], true],
        ];
    }

    protected function getValidator(): ConstraintValidatorInterface
    {
        $this->sectionServiceMock = $this->createMock(SectionService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, ['sudo', 'getSectionService']);

        $this->repositoryMock
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryMock),
            );

        $this->repositoryMock
            ->method('getSectionService')
            ->willReturn($this->sectionServiceMock);

        return new SectionValidator($this->repositoryMock);
    }
}
