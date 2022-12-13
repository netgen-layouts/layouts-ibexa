<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\TargetType;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Location as IbexaLocation;
use Netgen\Layouts\Ibexa\ContentProvider\ContentExtractorInterface;
use Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location;
use Netgen\Layouts\Ibexa\Tests\Validator\RepositoryValidatorFactory;
use Netgen\Layouts\Ibexa\Utils\RemoteIdConverter;
use Netgen\Layouts\Layout\Resolver\ValueObjectProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class LocationTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Ibexa\Contracts\Core\Repository\Repository
     */
    private MockObject $repositoryMock;

    private MockObject $locationServiceMock;

    private MockObject $contentExtractorMock;

    private MockObject $valueObjectProviderMock;

    private Location $targetType;

    protected function setUp(): void
    {
        $this->contentExtractorMock = $this->createMock(ContentExtractorInterface::class);
        $this->valueObjectProviderMock = $this->createMock(ValueObjectProviderInterface::class);
        $this->locationServiceMock = $this->createMock(LocationService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, ['sudo', 'getLocationService']);

        $this->repositoryMock
            ->expects(self::any())
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryMock),
            );

        $this->repositoryMock
            ->expects(self::any())
            ->method('getLocationService')
            ->willReturn($this->locationServiceMock);

        $this->targetType = new Location(
            $this->contentExtractorMock,
            $this->valueObjectProviderMock,
            new RemoteIdConverter($this->repositoryMock),
        );
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::getType
     */
    public function testGetType(): void
    {
        self::assertSame('ibexa_location', $this->targetType::getType());
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::getConstraints
     */
    public function testValidation(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn(new IbexaLocation());

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryMock))
            ->getValidator();

        $errors = $validator->validate(42, $this->targetType->getConstraints());
        self::assertCount(0, $errors);
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::getConstraints
     */
    public function testValidationWithInvalidValue(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('location', 42));

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryMock))
            ->getValidator();

        $errors = $validator->validate(42, $this->targetType->getConstraints());
        self::assertCount(0, $errors);
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::__construct
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::provideValue
     */
    public function testProvideValue(): void
    {
        $location = new IbexaLocation(
            [
                'id' => 42,
            ],
        );

        $request = Request::create('/');

        $this->contentExtractorMock
            ->expects(self::any())
            ->method('extractLocation')
            ->with(self::identicalTo($request))
            ->willReturn($location);

        self::assertSame(42, $this->targetType->provideValue($request));
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::__construct
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::provideValue
     */
    public function testProvideValueWithNoLocation(): void
    {
        $request = Request::create('/');

        $this->contentExtractorMock
            ->expects(self::any())
            ->method('extractLocation')
            ->with(self::identicalTo($request))
            ->willReturn(null);

        self::assertNull($this->targetType->provideValue($request));
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::getValueObject
     */
    public function testGetValueObject(): void
    {
        $location = new IbexaLocation();

        $this->valueObjectProviderMock
            ->expects(self::once())
            ->method('getValueObject')
            ->with(self::identicalTo(42))
            ->willReturn($location);

        self::assertSame($location, $this->targetType->getValueObject(42));
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::export
     */
    public function testExport(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn(new IbexaLocation(['remoteId' => 'abc']));

        self::assertSame('abc', $this->targetType->export(42));
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::export
     */
    public function testExportWithInvalidValue(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('location', 42));

        self::assertNull($this->targetType->export(42));
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::import
     */
    public function testImport(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willReturn(new IbexaLocation(['id' => 42]));

        self::assertSame(42, $this->targetType->import('abc'));
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location::import
     */
    public function testImportWithInvalidValue(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willThrowException(new NotFoundException('location', 'abc'));

        self::assertSame(0, $this->targetType->import('abc'));
    }
}
