<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Validator;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location as IbexaLocation;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Netgen\Layouts\Ibexa\Validator\Constraint\Location;
use Netgen\Layouts\Ibexa\Validator\LocationValidator;
use Netgen\Layouts\Tests\TestCase\ValidatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[CoversClass(LocationValidator::class)]
final class LocationValidatorTest extends ValidatorTestCase
{
    private Stub&Repository $repositoryStub;

    private Stub&LocationService $locationServiceStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new Location(allowedTypes: ['user']);
    }

    public function testValidateValid(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn(
                new IbexaLocation(
                    [
                        'id' => 42,
                        'content' => new Content(['contentType' => new ContentType(['identifier' => 'user'])]),
                    ],
                ),
            );

        $this->assertValid(true, 42);
    }

    public function testValidateInvalidWithWrongType(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn(
                new IbexaLocation(
                    [
                        'id' => 42,
                        'content' => new Content(['contentType' => new ContentType(['identifier' => 'article'])]),
                    ],
                ),
            );

        $this->assertValid(false, 42);
    }

    public function testValidateInvalidWithNonExistingLocation(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('location', 42));

        $this->assertValid(false, 42);
    }

    public function testValidateNull(): void
    {
        $this->assertValid(true, null);
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\Layouts\Ibexa\Validator\Constraint\Location", "Symfony\Component\Validator\Constraints\NotBlank" given');

        $this->constraint = new NotBlank();
        $this->assertValid(true, 'value');
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "scalar", "array" given');

        $this->assertValid(true, []);
    }

    protected function getValidator(): ConstraintValidatorInterface
    {
        $this->locationServiceStub = self::createStub(LocationService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryStub),
            );
        $this->repositoryStub
            ->method('getLocationService')
            ->willReturn($this->locationServiceStub);

        return new LocationValidator($this->repositoryStub);
    }
}
