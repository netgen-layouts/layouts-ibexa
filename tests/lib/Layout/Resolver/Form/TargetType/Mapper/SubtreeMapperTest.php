<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\ContentBrowser\Form\Type\ContentBrowserIntegerType;
use Netgen\Layouts\Ibexa\Layout\Resolver\Form\TargetType\Mapper\SubtreeMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SubtreeMapper::class)]
final class SubtreeMapperTest extends TestCase
{
    private SubtreeMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SubtreeMapper();
    }

    public function testGetFormType(): void
    {
        self::assertSame(ContentBrowserIntegerType::class, $this->mapper->getFormType());
    }

    public function testGetFormOptions(): void
    {
        self::assertSame(
            [
                'item_type' => 'ibexa_location',
            ],
            $this->mapper->getFormOptions(),
        );
    }
}
