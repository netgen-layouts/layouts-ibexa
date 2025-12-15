<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\Form\ConditionType\Mapper;

use Netgen\Layouts\Ibexa\Form\ContentTypeType;
use Netgen\Layouts\Ibexa\Layout\Resolver\Form\ConditionType\Mapper\ContentTypeMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentTypeMapper::class)]
final class ContentTypeMapperTest extends TestCase
{
    private ContentTypeMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ContentTypeMapper();
    }

    public function testGetFormType(): void
    {
        self::assertSame(ContentTypeType::class, $this->mapper->getFormType());
    }

    public function testGetFormOptions(): void
    {
        self::assertSame(
            [
                'multiple' => true,
            ],
            $this->mapper->getFormOptions(),
        );
    }
}
