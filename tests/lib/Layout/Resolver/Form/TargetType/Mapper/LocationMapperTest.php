<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\ContentBrowser\Form\Type\ContentBrowserIntegerType;
use Netgen\Layouts\Ibexa\Layout\Resolver\Form\TargetType\Mapper\LocationMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocationMapper::class)]
final class LocationMapperTest extends TestCase
{
    private LocationMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new LocationMapper();
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
