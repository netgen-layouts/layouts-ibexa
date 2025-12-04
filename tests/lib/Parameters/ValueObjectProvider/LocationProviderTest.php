<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\ValueObjectProvider;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Error\ErrorHandlerInterface;
use Netgen\Layouts\Ibexa\Parameters\ValueObjectProvider\LocationProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocationProvider::class)]
final class LocationProviderTest extends TestCase
{
    private Stub&Repository $repositoryStub;

    private Stub&LocationService $locationServiceStub;

    private LocationProvider $valueObjectProvider;

    protected function setUp(): void
    {
        $this->repositoryStub = self::createStub(Repository::class);
        $this->locationServiceStub = self::createStub(LocationService::class);

        $this->repositoryStub
            ->method('getLocationService')
            ->willReturn($this->locationServiceStub);

        $this->repositoryStub
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryStub),
            );

        $this->valueObjectProvider = new LocationProvider(
            $this->repositoryStub,
            self::createStub(ErrorHandlerInterface::class),
        );
    }

    public function testGetValueObject(): void
    {
        $location = new Location();

        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn($location);

        self::assertSame($location, $this->valueObjectProvider->getValueObject(42));
    }

    public function testGetValueObjectWithNullValue(): void
    {
        self::assertNull($this->valueObjectProvider->getValueObject(null));
    }

    public function testGetValueObjectWithNonExistentLocation(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('location', 42));

        self::assertNull($this->valueObjectProvider->getValueObject(42));
    }
}
