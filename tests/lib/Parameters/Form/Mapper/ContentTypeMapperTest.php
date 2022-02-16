<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\Form\Mapper;

use Netgen\Layouts\Ibexa\Form\ContentTypeType;
use Netgen\Layouts\Ibexa\Parameters\Form\Mapper\ContentTypeMapper;
use Netgen\Layouts\Ibexa\Parameters\ParameterType\ContentTypeType as ParameterType;
use Netgen\Layouts\Parameters\ParameterDefinition;
use PHPUnit\Framework\TestCase;

final class ContentTypeMapperTest extends TestCase
{
    private ContentTypeMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ContentTypeMapper();
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Parameters\Form\Mapper\ContentTypeMapper::getFormType
     */
    public function testGetFormType(): void
    {
        self::assertSame(ContentTypeType::class, $this->mapper->getFormType());
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Parameters\Form\Mapper\ContentTypeMapper::mapOptions
     */
    public function testMapOptions(): void
    {
        self::assertSame(
            [
                'multiple' => true,
                'types' => [42],
            ],
            $this->mapper->mapOptions(
                ParameterDefinition::fromArray(
                    [
                        'type' => new ParameterType(),
                        'options' => [
                            'multiple' => true,
                            'types' => [42],
                        ],
                    ],
                ),
            ),
        );
    }
}
