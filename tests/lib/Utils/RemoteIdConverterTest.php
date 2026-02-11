<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Utils;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Ibexa\Utils\RemoteIdConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoteIdConverter::class)]
final class RemoteIdConverterTest extends TestCase
{
    private Stub&LocationService $locationServiceStub;

    private Stub&ContentService $contentServiceStub;

    private RemoteIdConverter $converter;

    protected function setUp(): void
    {
        $this->locationServiceStub = self::createStub(LocationService::class);
        $this->contentServiceStub = self::createStub(ContentService::class);

        $repositoryStub = self::createStub(Repository::class);
        $repositoryStub
            ->method('sudo')
            ->willReturnCallback(
                static fn (callable $callback): mixed => $callback($repositoryStub),
            );

        $repositoryStub
            ->method('getLocationService')
            ->willReturn($this->locationServiceStub);

        $repositoryStub
            ->method('getContentService')
            ->willReturn($this->contentServiceStub);

        $this->converter = new RemoteIdConverter($repositoryStub);
    }

    public function testToLocationId(): void
    {
        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->willReturn(new Location(['id' => 42]));

        self::assertSame(42, $this->converter->toLocationId('abc'));
    }

    public function testToLocationIdWithNonExistentRemoteId(): void
    {
        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->willThrowException(new NotFoundException('location', 'abc'));

        self::assertNull($this->converter->toLocationId('abc'));
    }

    public function testToLocationRemoteId(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willReturn(new Location(['remoteId' => 'abc']));

        self::assertSame('abc', $this->converter->toLocationRemoteId(42));
    }

    public function testToLocationRemoteIdWithNonExistentId(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willThrowException(new NotFoundException('location', 42));

        self::assertNull($this->converter->toLocationRemoteId(42));
    }

    public function testToContentId(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willReturn(new ContentInfo(['id' => 42]));

        self::assertSame(42, $this->converter->toContentId('abc'));
    }

    public function testToContentIdWithNonExistentRemoteId(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willThrowException(new NotFoundException('content', 'abc'));

        self::assertNull($this->converter->toContentId('abc'));
    }

    public function testToContentRemoteId(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willReturn(new ContentInfo(['remoteId' => 'abc']));

        self::assertSame('abc', $this->converter->toContentRemoteId(42));
    }

    public function testToContentRemoteIdWithNonExistentId(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willThrowException(new NotFoundException('content', 42));

        self::assertNull($this->converter->toContentRemoteId(42));
    }
}
