<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Item\ValueUrlGenerator;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Netgen\Layouts\Ibexa\Item\ValueUrlGenerator\ContentValueUrlGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(ContentValueUrlGenerator::class)]
final class ContentValueUrlGeneratorTest extends TestCase
{
    private Stub&UrlGeneratorInterface $urlGeneratorStub;

    private ContentValueUrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGeneratorStub = self::createStub(UrlGeneratorInterface::class);

        $this->urlGenerator = new ContentValueUrlGenerator($this->urlGeneratorStub);
    }

    public function testGenerateDefaultUrl(): void
    {
        $this->urlGeneratorStub
            ->method('generate')
            ->willReturn('/content/path');

        self::assertSame('/content/path', $this->urlGenerator->generateDefaultUrl(new ContentInfo(['id' => 42])));
    }

    public function testGenerateAdminUrl(): void
    {
        $this->urlGeneratorStub
            ->method('generate')
            ->willReturn('/admin/content/path');

        self::assertSame('/admin/content/path', $this->urlGenerator->generateAdminUrl(new ContentInfo(['id' => 42])));
    }
}
