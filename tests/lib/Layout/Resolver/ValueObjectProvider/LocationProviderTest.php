<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\ValueObjectProvider;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Ibexa\Layout\Resolver\ValueObjectProvider\LocationProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocationProvider::class)]
final class LocationProviderTest extends TestCase
{
    private Stub&LocationService $locationServiceStub;

    private LocationProvider $valueObjectProvider;

    protected function setUp(): void
    {
        $this->locationServiceStub = self::createStub(LocationService::class);

        $repositoryStub = self::createStub(Repository::class);
        $repositoryStub
            ->method('getLocationService')
            ->willReturn($this->locationServiceStub);

        $repositoryStub
            ->method('sudo')
            ->willReturnCallback(
                static fn (callable $callback): mixed => $callback($repositoryStub),
            );

        $this->valueObjectProvider = new LocationProvider($repositoryStub);
    }

    public function testGetValueObject(): void
    {
        $location = new Location();

        $this->locationServiceStub
            ->method('loadLocation')
            ->willReturn($location);

        self::assertSame($location, $this->valueObjectProvider->getValueObject(42));
    }

    public function testGetValueObjectWithNonExistentLocation(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willThrowException(new NotFoundException('location', 42));

        self::assertNull($this->valueObjectProvider->getValueObject(42));
    }
}
