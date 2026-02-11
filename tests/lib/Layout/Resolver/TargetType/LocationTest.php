<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\TargetType;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Location as IbexaLocation;
use Netgen\Layouts\Ibexa\ContentProvider\ContentExtractorInterface;
use Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Location;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use Netgen\Layouts\Ibexa\Utils\RemoteIdConverter;
use Netgen\Layouts\Layout\Resolver\ValueObjectProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Location::class)]
final class LocationTest extends TestCase
{
    use ValidatorTestCaseTrait;

    private Stub&Repository $repositoryStub;

    private Stub&LocationService $locationServiceStub;

    private Stub&ContentExtractorInterface $contentExtractorStub;

    private Stub&ValueObjectProviderInterface $valueObjectProviderStub;

    private Location $targetType;

    protected function setUp(): void
    {
        $this->contentExtractorStub = self::createStub(ContentExtractorInterface::class);
        $this->valueObjectProviderStub = self::createStub(ValueObjectProviderInterface::class);
        $this->locationServiceStub = self::createStub(LocationService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getLocationService')
            ->willReturn($this->locationServiceStub);

        $this->targetType = new Location(
            $this->contentExtractorStub,
            $this->valueObjectProviderStub,
            new RemoteIdConverter($this->repositoryStub),
        );
    }

    public function testGetType(): void
    {
        self::assertSame('ibexa_location', $this->targetType::getType());
    }

    public function testValidation(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willReturn(new IbexaLocation());

        $validator = $this->createValidator($this->repositoryStub);

        $errors = $validator->validate(42, $this->targetType->getConstraints());
        self::assertCount(0, $errors);
    }

    public function testValidationWithInvalidValue(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willThrowException(new NotFoundException('location', 42));

        $validator = $this->createValidator($this->repositoryStub);

        $errors = $validator->validate(42, $this->targetType->getConstraints());
        self::assertCount(0, $errors);
    }

    public function testProvideValue(): void
    {
        $location = new IbexaLocation(
            [
                'id' => 42,
            ],
        );

        $request = Request::create('/');

        $this->contentExtractorStub
            ->method('extractLocation')
            ->willReturn($location);

        self::assertSame(42, $this->targetType->provideValue($request));
    }

    public function testProvideValueWithNoLocation(): void
    {
        $request = Request::create('/');

        $this->contentExtractorStub
            ->method('extractLocation')
            ->willReturn(null);

        self::assertNull($this->targetType->provideValue($request));
    }

    public function testGetValueObject(): void
    {
        $location = new IbexaLocation();

        $this->valueObjectProviderStub
            ->method('getValueObject')
            ->willReturn($location);

        self::assertSame($location, $this->targetType->getValueObject(42));
    }

    public function testExport(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willReturn(new IbexaLocation(['remoteId' => 'abc']));

        self::assertSame('abc', $this->targetType->export(42));
    }

    public function testExportWithInvalidValue(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willThrowException(new NotFoundException('location', 42));

        self::assertNull($this->targetType->export(42));
    }

    public function testImport(): void
    {
        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->willReturn(new IbexaLocation(['id' => 42]));

        self::assertSame(42, $this->targetType->import('abc'));
    }

    public function testImportWithInvalidValue(): void
    {
        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->willThrowException(new NotFoundException('location', 'abc'));

        self::assertSame(0, $this->targetType->import('abc'));
    }
}
