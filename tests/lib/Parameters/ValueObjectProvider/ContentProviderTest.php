<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\ValueObjectProvider;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Netgen\Layouts\Error\ErrorHandlerInterface;
use Netgen\Layouts\Ibexa\Parameters\ValueObjectProvider\ContentProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentProvider::class)]
final class ContentProviderTest extends TestCase
{
    private Stub&Repository $repositoryStub;

    private Stub&ContentService $contentServiceStub;

    private ContentProvider $valueObjectProvider;

    protected function setUp(): void
    {
        $this->repositoryStub = self::createStub(Repository::class);
        $this->contentServiceStub = self::createStub(ContentService::class);

        $this->repositoryStub
            ->method('getContentService')
            ->willReturn($this->contentServiceStub);

        $this->repositoryStub
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryStub),
            );

        $this->valueObjectProvider = new ContentProvider(
            $this->repositoryStub,
            self::createStub(ErrorHandlerInterface::class),
        );
    }

    public function testGetValueObject(): void
    {
        $content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(['mainLocationId' => 24]),
                    ],
                ),
            ],
        );

        $this->contentServiceStub
            ->method('loadContent')
            ->with(self::identicalTo(42))
            ->willReturn($content);

        self::assertSame($content, $this->valueObjectProvider->getValueObject(42));
    }

    public function testGetValueObjectWithNullValue(): void
    {
        self::assertNull($this->valueObjectProvider->getValueObject(null));
    }

    public function testGetValueObjectWithNonExistentLocation(): void
    {
        $this->contentServiceStub
            ->method('loadContent')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('content', 42));

        self::assertNull($this->valueObjectProvider->getValueObject(42));
    }

    public function testGetValueObjectWithNoMainLocation(): void
    {
        $content = new Content(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(),
                    ],
                ),
            ],
        );

        $this->contentServiceStub
            ->method('loadContent')
            ->with(self::identicalTo(42))
            ->willReturn($content);

        self::assertNull($this->valueObjectProvider->getValueObject(42));
    }
}
