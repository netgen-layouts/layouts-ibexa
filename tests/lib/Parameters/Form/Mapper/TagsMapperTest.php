<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\Form\Mapper;

use Netgen\ContentBrowser\Form\Type\ContentBrowserMultipleType;
use Netgen\Layouts\Ibexa\Parameters\Form\Mapper\TagsMapper;
use Netgen\Layouts\Ibexa\Parameters\ParameterType\TagsType as ParameterType;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\TagsBundle\API\Repository\TagsService;
use PHPUnit\Framework\TestCase;

final class TagsMapperTest extends TestCase
{
    private TagsMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new TagsMapper();
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Parameters\Form\Mapper\TagsMapper::getFormType
     */
    public function testGetFormType(): void
    {
        self::assertSame(ContentBrowserMultipleType::class, $this->mapper->getFormType());
    }

    /**
     * @covers \Netgen\Layouts\Ibexa\Parameters\Form\Mapper\TagsMapper::mapOptions
     */
    public function testMapOptions(): void
    {
        self::assertSame(
            [
                'item_type' => 'netgen_tags',
                'min' => 3,
                'max' => 6,
            ],
            $this->mapper->mapOptions(
                ParameterDefinition::fromArray(
                    [
                        'type' => new ParameterType($this->createMock(TagsService::class)),
                        'options' => [
                            'min' => 3,
                            'max' => 6,
                        ],
                    ],
                ),
            ),
        );
    }
}
