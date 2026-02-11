<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\TargetType;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Netgen\Layouts\Ibexa\ContentProvider\ContentExtractorInterface;
use Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\Content;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use Netgen\Layouts\Ibexa\Utils\RemoteIdConverter;
use Netgen\Layouts\Layout\Resolver\ValueObjectProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Content::class)]
final class ContentTest extends TestCase
{
    use ValidatorTestCaseTrait;

    private Stub&Repository $repositoryStub;

    private Stub&ContentService $contentServiceStub;

    private Stub&ContentExtractorInterface $contentExtractorStub;

    private Stub&ValueObjectProviderInterface $valueObjectProviderStub;

    private Content $targetType;

    protected function setUp(): void
    {
        $this->contentExtractorStub = self::createStub(ContentExtractorInterface::class);
        $this->valueObjectProviderStub = self::createStub(ValueObjectProviderInterface::class);
        $this->contentServiceStub = self::createStub(ContentService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getContentService')
            ->willReturn($this->contentServiceStub);

        $this->targetType = new Content(
            $this->contentExtractorStub,
            $this->valueObjectProviderStub,
            new RemoteIdConverter($this->repositoryStub),
        );
    }

    public function testGetType(): void
    {
        self::assertSame('ibexa_content', $this->targetType::getType());
    }

    public function testValidation(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willReturn(new ContentInfo());

        $validator = $this->createValidator($this->repositoryStub);

        $errors = $validator->validate(42, $this->targetType->getConstraints());
        self::assertCount(0, $errors);
    }

    public function testValidationWithInvalidValue(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willThrowException(new NotFoundException('content', 42));

        $validator = $this->createValidator($this->repositoryStub);

        $errors = $validator->validate(42, $this->targetType->getConstraints());
        self::assertCount(0, $errors);
    }

    public function testProvideValue(): void
    {
        $content = new IbexaContent(
            [
                'versionInfo' => new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'id' => 42,
                            ],
                        ),
                    ],
                ),
            ],
        );

        $request = Request::create('/');

        $this->contentExtractorStub
            ->method('extractContent')
            ->willReturn($content);

        self::assertSame(42, $this->targetType->provideValue($request));
    }

    public function testProvideValueWithNoContent(): void
    {
        $request = Request::create('/');

        $this->contentExtractorStub
            ->method('extractContent')
            ->willReturn(null);

        self::assertNull($this->targetType->provideValue($request));
    }

    public function testGetValueObject(): void
    {
        $content = new IbexaContent();

        $this->valueObjectProviderStub
            ->method('getValueObject')
            ->willReturn($content);

        self::assertSame($content, $this->targetType->getValueObject(42));
    }

    public function testExport(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willReturn(new ContentInfo(['remoteId' => 'abc']));

        self::assertSame('abc', $this->targetType->export(42));
    }

    public function testExportWithInvalidValue(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willThrowException(new NotFoundException('content', 42));

        self::assertNull($this->targetType->export(42));
    }

    public function testImport(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willReturn(new ContentInfo(['id' => 42]));

        self::assertSame(42, $this->targetType->import('abc'));
    }

    public function testImportWithInvalidValue(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willThrowException(new NotFoundException('content', 'abc'));

        self::assertSame(0, $this->targetType->import('abc'));
    }
}
