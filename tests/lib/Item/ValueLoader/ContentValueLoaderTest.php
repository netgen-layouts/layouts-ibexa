<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Item\ValueLoader;

use Exception;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Netgen\Layouts\Ibexa\Item\ValueLoader\ContentValueLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentValueLoader::class)]
final class ContentValueLoaderTest extends TestCase
{
    private Stub&ContentService $contentServiceStub;

    private ContentValueLoader $valueLoader;

    protected function setUp(): void
    {
        $this->contentServiceStub = self::createStub(ContentService::class);

        $this->valueLoader = new ContentValueLoader($this->contentServiceStub);
    }

    public function testLoad(): void
    {
        $contentInfo = new ContentInfo(
            [
                'id' => 52,
                'published' => true,
                'mainLocationId' => 42,
            ],
        );

        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willReturn($contentInfo);

        self::assertSame($contentInfo, $this->valueLoader->load(52));
    }

    public function testLoadWithNoContent(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willThrowException(new Exception());

        self::assertNull($this->valueLoader->load(52));
    }

    public function testLoadWithNonPublishedContent(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willReturn(
                new ContentInfo(
                    [
                        'published' => false,
                        'mainLocationId' => 42,
                    ],
                ),
            );

        self::assertNull($this->valueLoader->load(52));
    }

    public function testLoadWithNoMainLocation(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willReturn(
                new ContentInfo(
                    [
                        'published' => true,
                    ],
                ),
            );

        self::assertNull($this->valueLoader->load(52));
    }

    public function testLoadByRemoteId(): void
    {
        $contentInfo = new ContentInfo(
            [
                'remoteId' => 'abc',
                'published' => true,
                'mainLocationId' => 42,
            ],
        );

        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willReturn($contentInfo);

        self::assertSame($contentInfo, $this->valueLoader->loadByRemoteId('abc'));
    }

    public function testLoadByRemoteIdWithNoContent(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willThrowException(new Exception());

        self::assertNull($this->valueLoader->loadByRemoteId('abc'));
    }

    public function testLoadByRemoteIdWithNonPublishedContent(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willReturn(
                new ContentInfo(
                    [
                        'published' => false,
                        'mainLocationId' => 42,
                    ],
                ),
            );

        self::assertNull($this->valueLoader->loadByRemoteId('abc'));
    }

    public function testLoadByRemoteIdWithNoMainLocation(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willReturn(
                new ContentInfo(
                    [
                        'published' => true,
                    ],
                ),
            );

        self::assertNull($this->valueLoader->loadByRemoteId('abc'));
    }
}
