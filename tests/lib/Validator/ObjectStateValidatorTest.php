<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Validator;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\ObjectState\ObjectState as IbexaObjectState;
use Ibexa\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Netgen\Layouts\Ibexa\Validator\Constraint\ObjectState;
use Netgen\Layouts\Ibexa\Validator\ObjectStateValidator;
use Netgen\Layouts\Tests\TestCase\ValidatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[CoversClass(ObjectStateValidator::class)]
final class ObjectStateValidatorTest extends ValidatorTestCase
{
    private Stub&Repository $repositoryStub;

    private Stub&ObjectStateService $objectStateServiceStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new ObjectState();
    }

    /**
     * @param string[] $allowedStates
     */
    #[DataProvider('validateDataProvider')]
    public function testValidate(string $identifier, array $allowedStates, bool $isValid): void
    {
        $group1 = new ObjectStateGroup(['identifier' => 'group1']);
        $group2 = new ObjectStateGroup(['identifier' => 'group2']);

        $this->objectStateServiceStub
            ->method('loadObjectStateGroups')
            ->willReturn([$group1, $group2]);

        $this->objectStateServiceStub
            ->method('loadObjectStates')
            ->willReturnMap(
                [
                    [
                        $group1,
                        [],
                        [
                            new IbexaObjectState(
                                [
                                    'identifier' => 'state1',
                                ],
                            ),
                            new IbexaObjectState(
                                [
                                    'identifier' => 'state2',
                                ],
                            ),
                        ],
                    ],
                    [
                        $group2,
                        [],
                        [
                            new IbexaObjectState(
                                [
                                    'identifier' => 'state1',
                                ],
                            ),
                            new IbexaObjectState(
                                [
                                    'identifier' => 'state2',
                                ],
                            ),
                        ],
                    ],
                ],
            );

        $this->constraint->allowedStates = $allowedStates;
        $this->assertValid($isValid, $identifier);
    }

    public function testValidateNull(): void
    {
        $this->assertValid(true, null);
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\Layouts\Ibexa\Validator\Constraint\ObjectState", "Symfony\Component\Validator\Constraints\NotBlank" given');

        $this->constraint = new NotBlank();
        $this->assertValid(true, 'value');
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "int" given');

        $this->assertValid(true, 42);
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValueFormat(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "string with "|" delimiter", "string" given');

        $this->assertValid(true, 'state');
    }

    /**
     * @return iterable<mixed>
     */
    public static function validateDataProvider(): iterable
    {
        return [
            ['group1|state1', [], true],
            ['group1|state1', ['group2' => true], true],
            ['group1|state1', ['group1' => true], true],
            ['group1|state1', ['group1' => false], false],
            ['group1|state1', ['group1' => []], false],
            ['group1|state1', ['group1' => ['state1']], true],
            ['group1|state1', ['group1' => ['state2']], false],
            ['group1|state1', ['group1' => ['state1', 'state2']], true],
            ['group2|state1', [], true],
            ['group2|state1', ['group2' => true], true],
            ['group2|state1', ['group1' => true], true],
            ['group2|state1', ['group1' => false], true],
            ['group2|state1', ['group1' => []], true],
            ['group2|state1', ['group1' => ['state1']], true],
            ['group2|state1', ['group1' => ['state2']], true],
            ['group2|state1', ['group1' => ['state1', 'state2']], true],
            ['unknown|state1', [], false],
            ['group1|unknown', [], false],
        ];
    }

    protected function getConstraintValidator(): ConstraintValidatorInterface
    {
        $this->objectStateServiceStub = self::createStub(ObjectStateService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getObjectStateService')
            ->willReturn($this->objectStateServiceStub);

        return new ObjectStateValidator($this->repositoryStub);
    }
}
