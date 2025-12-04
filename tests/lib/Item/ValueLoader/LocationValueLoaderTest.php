<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Item\ValueLoader;

use Exception;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Ibexa\Item\ValueLoader\LocationValueLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocationValueLoader::class)]
final class LocationValueLoaderTest extends TestCase
{
    private Stub&LocationService $locationServiceStub;

    private LocationValueLoader $valueLoader;

    protected function setUp(): void
    {
        $this->locationServiceStub = self::createStub(LocationService::class);

        $this->valueLoader = new LocationValueLoader($this->locationServiceStub);
    }

    public function testLoad(): void
    {
        $location = new Location(
            [
                'id' => 52,
                'contentInfo' => new ContentInfo(
                    [
                        'published' => true,
                    ],
                ),
            ],
        );

        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(52))
            ->willReturn($location);

        self::assertSame($location, $this->valueLoader->load(52));
    }

    public function testLoadWithNoLocation(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(52))
            ->willThrowException(new Exception());

        self::assertNull($this->valueLoader->load(52));
    }

    public function testLoadWithNonPublishedContent(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(52))
            ->willReturn(
                new Location(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'published' => false,
                            ],
                        ),
                    ],
                ),
            );

        self::assertNull($this->valueLoader->load(52));
    }

    public function testLoadByRemoteId(): void
    {
        $location = new Location(
            [
                'remoteId' => 'abc',
                'contentInfo' => new ContentInfo(
                    [
                        'published' => true,
                    ],
                ),
            ],
        );

        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willReturn($location);

        self::assertSame($location, $this->valueLoader->loadByRemoteId('abc'));
    }

    public function testLoadByRemoteIdWithNoLocation(): void
    {
        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willThrowException(new Exception());

        self::assertNull($this->valueLoader->loadByRemoteId('abc'));
    }

    public function testLoadByRemoteIdWithNonPublishedContent(): void
    {
        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willReturn(
                new Location(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'published' => false,
                            ],
                        ),
                    ],
                ),
            );

        self::assertNull($this->valueLoader->loadByRemoteId('abc'));
    }
}
