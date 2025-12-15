<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\ContentBrowser\Form\Type\ContentBrowserIntegerType;
use Netgen\Layouts\Ibexa\Layout\Resolver\Form\TargetType\Mapper\ContentMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentMapper::class)]
final class ContentMapperTest extends TestCase
{
    private ContentMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ContentMapper();
    }

    public function testGetFormType(): void
    {
        self::assertSame(ContentBrowserIntegerType::class, $this->mapper->getFormType());
    }

    public function testGetFormOptions(): void
    {
        self::assertSame(
            [
                'item_type' => 'ibexa_content',
            ],
            $this->mapper->getFormOptions(),
        );
    }
}
