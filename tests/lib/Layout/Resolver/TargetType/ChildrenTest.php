<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\TargetType;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Ibexa\ContentProvider\ContentExtractorInterface;
use Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Children;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use Netgen\Layouts\Ibexa\Utils\RemoteIdConverter;
use Netgen\Layouts\Layout\Resolver\ValueObjectProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Children::class)]
final class ChildrenTest extends TestCase
{
    use ValidatorTestCaseTrait;

    private Stub&Repository $repositoryStub;

    private Stub&ContentExtractorInterface $contentExtractorStub;

    private Stub&ValueObjectProviderInterface $valueObjectProviderStub;

    private Children $targetType;

    private Stub&LocationService $locationServiceStub;

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

        $this->targetType = new Children(
            $this->contentExtractorStub,
            $this->valueObjectProviderStub,
            new RemoteIdConverter($this->repositoryStub),
        );
    }

    public function testGetType(): void
    {
        self::assertSame('ibexa_children', $this->targetType::getType());
    }

    public function testValidation(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willReturn(new Location());

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
        $location = new Location(
            [
                'parentLocationId' => 84,
            ],
        );

        $request = Request::create('/');

        $this->contentExtractorStub
            ->method('extractLocation')
            ->willReturn($location);

        self::assertSame(84, $this->targetType->provideValue($request));
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
        $location = new Location();

        $this->valueObjectProviderStub
            ->method('getValueObject')
            ->willReturn($location);

        self::assertSame($location, $this->targetType->getValueObject(42));
    }

    public function testExport(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willReturn(new Location(['remoteId' => 'abc']));

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
            ->willReturn(new Location(['id' => 42]));

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
