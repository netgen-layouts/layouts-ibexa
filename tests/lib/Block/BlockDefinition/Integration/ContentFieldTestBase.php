<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Block\BlockDefinition\Integration;

use Netgen\Layouts\Block\BlockDefinition\BlockDefinitionHandlerInterface;
use Netgen\Layouts\Ibexa\Block\BlockDefinition\Handler\ContentFieldHandler;
use Netgen\Layouts\Ibexa\ContentProvider\ContentProviderInterface;
use Netgen\Layouts\Tests\Block\BlockDefinition\Integration\BlockTestCase;

abstract class ContentFieldTestBase extends BlockTestCase
{
    final public static function parametersDataProvider(): iterable
    {
        return [
            [
                [],
                [
                    'field_identifier' => null,
                ],
            ],
            [
                [
                    'field_identifier' => null,
                ],
                [
                    'field_identifier' => null,
                ],
            ],
            [
                [
                    'field_identifier' => '',
                ],
                [
                    'field_identifier' => '',
                ],
            ],
            [
                [
                    'field_identifier' => 'title',
                ],
                [
                    'field_identifier' => 'title',
                ],
            ],
            [
                [
                    'unknown' => 'unknown',
                ],
                [],
            ],
        ];
    }

    final public static function invalidParametersDataProvider(): iterable
    {
        return [
            [
                [
                    'field_identifier' => 42,
                ],
            ],
        ];
    }

    final protected function createBlockDefinitionHandler(): BlockDefinitionHandlerInterface
    {
        return new ContentFieldHandler(self::createStub(ContentProviderInterface::class));
    }
}
