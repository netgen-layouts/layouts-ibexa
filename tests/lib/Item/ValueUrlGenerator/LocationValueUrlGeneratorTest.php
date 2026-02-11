<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Item\ValueUrlGenerator;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Ibexa\Item\ValueUrlGenerator\LocationValueUrlGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(LocationValueUrlGenerator::class)]
final class LocationValueUrlGeneratorTest extends TestCase
{
    private Stub&UrlGeneratorInterface $urlGeneratorStub;

    private LocationValueUrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGeneratorStub = self::createStub(UrlGeneratorInterface::class);

        $this->urlGenerator = new LocationValueUrlGenerator($this->urlGeneratorStub);
    }

    public function testGenerateDefaultUrl(): void
    {
        $this->urlGeneratorStub
            ->method('generate')
            ->willReturn('/location/path');

        self::assertSame('/location/path', $this->urlGenerator->generateDefaultUrl(new Location(['id' => 42])));
    }

    public function testGenerateAdminUrl(): void
    {
        $this->urlGeneratorStub
            ->method('generate')
            ->willReturn('/admin/location/path');

        $location = new Location(['id' => 42, 'contentInfo' => new ContentInfo(['id' => 24])]);

        self::assertSame('/admin/location/path', $this->urlGenerator->generateAdminUrl($location));
    }
}
