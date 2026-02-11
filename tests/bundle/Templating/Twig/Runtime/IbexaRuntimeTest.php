<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Tests\Templating\Twig\Runtime;

use Exception;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Netgen\Bundle\LayoutsIbexaBundle\Templating\Twig\Runtime\IbexaRuntime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(IbexaRuntime::class)]
final class IbexaRuntimeTest extends TestCase
{
    private Stub&Repository $repositoryStub;

    private Stub&ContentService $contentServiceStub;

    private Stub&LocationService $locationServiceStub;

    private Stub&ContentTypeService $contentTypeServiceStub;

    private IbexaRuntime $runtime;

    protected function setUp(): void
    {
        $this->prepareRepositoryStub();

        $this->runtime = new IbexaRuntime(
            $this->repositoryStub,
        );
    }

    public function testGetContentName(): void
    {
        $this->configureStubs();

        self::assertSame('Content name 42', $this->runtime->getContentName(42));
    }

    public function testGetContentNameWithException(): void
    {
        $this->contentServiceStub
            ->method('loadContent')
            ->willThrowException(new Exception());

        self::assertSame('', $this->runtime->getContentName(42));
    }

    public function testGetLocationPath(): void
    {
        $this->configureStubs();

        self::assertSame(
            [
                'Content name 102',
                'Content name 142',
                'Content name 184',
            ],
            $this->runtime->getLocationPath(22),
        );
    }

    public function testGetLocationPathWithException(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willThrowException(new Exception());

        self::assertSame([], $this->runtime->getLocationPath(22));
    }

    public function testGetContentPath(): void
    {
        $this->configureStubs();

        self::assertSame(
            [
                'Content name 102',
                'Content name 142',
                'Content name 184',
            ],
            $this->runtime->getContentPath(122),
        );
    }

    public function testGetContentPathWithException(): void
    {
        $this->contentServiceStub
            ->method('loadContent')
            ->willThrowException(new Exception());

        self::assertSame([], $this->runtime->getContentPath(22));
    }

    public function testGetContentTypeName(): void
    {
        $this->configureStubs();

        $this->contentTypeServiceStub
            ->method('loadContentTypeByIdentifier')
            ->willReturnCallback(
                static fn (string $identifier): ContentType => new ContentType(
                    [
                        'identifier' => $identifier,
                        'names' => [
                            'eng-GB' => 'English content type ' . $identifier,
                            'cro-HR' => 'Content type ' . $identifier,
                        ],
                        'mainLanguageCode' => 'cro-HR',
                    ],
                ),
            );

        self::assertSame('Content type some_type', $this->runtime->getContentTypeName('some_type'));
    }

    public function testGetContentTypeNameWithNoTranslatedName(): void
    {
        $this->configureStubs();

        $this->contentTypeServiceStub
            ->method('loadContentTypeByIdentifier')
            ->willReturnCallback(
                static fn (string $identifier): ContentType => new ContentType(
                    [
                        'identifier' => $identifier,
                        'names' => [
                            'eng-GB' => 'English content type ' . $identifier,
                            'cro-HR' => 'Content type ' . $identifier,
                        ],
                        'mainLanguageCode' => 'eng-GB',
                    ],
                ),
            );

        self::assertSame('English content type some_type', $this->runtime->getContentTypeName('some_type'));
    }

    public function testGetContentTypeNameWithException(): void
    {
        $this->contentTypeServiceStub
            ->method('loadContentTypeByIdentifier')
            ->willThrowException(new Exception());

        self::assertSame('', $this->runtime->getContentTypeName('some_type'));
    }

    private function prepareRepositoryStub(): void
    {
        $this->locationServiceStub = self::createStub(LocationService::class);
        $this->contentServiceStub = self::createStub(ContentService::class);
        $this->contentTypeServiceStub = self::createStub(ContentTypeService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getLocationService')
            ->willReturn($this->locationServiceStub);

        $this->repositoryStub
            ->method('getContentService')
            ->willReturn($this->contentServiceStub);

        $this->repositoryStub
            ->method('getContentTypeService')
            ->willReturn($this->contentTypeServiceStub);
    }

    private function configureStubs(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->willReturnCallback(
                static fn (int $locationId): Location => new Location(
                    [
                        'path' => [1, 2, 42, 84],
                        'contentInfo' => new ContentInfo(
                            [
                                'id' => $locationId + 100,
                            ],
                        ),
                    ],
                ),
            );

        $this->contentServiceStub
            ->method('loadContent')
            ->willReturnCallback(
                static fn (int $contentId): Content => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(
                                    [
                                        'mainLocationId' => $contentId - 100,
                                    ],
                                ),
                                'prioritizedNameLanguageCode' => 'eng-GB',
                                'names' => ['eng-GB' => 'Content name ' . $contentId],
                            ],
                        ),
                    ],
                ),
            );
    }
}
