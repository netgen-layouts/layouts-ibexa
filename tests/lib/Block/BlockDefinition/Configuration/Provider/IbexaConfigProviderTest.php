<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Block\BlockDefinition\Configuration\Provider;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\BlockDefinition\Configuration\ViewType;
use Netgen\Layouts\Ibexa\Block\BlockDefinition\Configuration\Provider\IbexaConfigProvider;
use Netgen\Layouts\Parameters\Parameter;
use Netgen\Layouts\Parameters\ParameterList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

#[CoversClass(IbexaConfigProvider::class)]
final class IbexaConfigProviderTest extends TestCase
{
    private Stub&ConfigResolverInterface $configResolverStub;

    private IbexaConfigProvider $configProvider;

    protected function setUp(): void
    {
        $this->configResolverStub = self::createStub(ConfigResolverInterface::class);

        $this->configProvider = new IbexaConfigProvider(
            $this->configResolverStub,
            [
                'cro' => ['group1', 'group2'],
                'admin' => ['admin_group'],
            ],
            'content_type_identifier',
            'content_view',
        );
    }

    public function testProvideViewTypes(): void
    {
        $blockUuid = Uuid::uuid4();
        $block = Block::fromArray(
            [
                'id' => $blockUuid,
                'parameters' => new ParameterList(
                    [
                        'content_type_identifier' => Parameter::fromArray(
                            [
                                'value' => 'foo',
                            ],
                        ),
                    ],
                ),
            ],
        );

        $this->configResolverStub
            ->method('getParameter')
            ->with(self::identicalTo('content_view'), self::isNull(), self::identicalTo('cro'))
            ->willReturn(
                [
                    'view_style_1' => [
                        'foo' => [
                            'template' => '@templates/foo.html.twig',
                            'match' => [
                                'Identifier\ContentType' => [
                                    'foo',
                                ],
                            ],
                            'params' => [
                                'valid_parameters' => ['param1'],
                            ],
                        ],
                        'foo2' => [
                            'template' => '@templates/foo2.html.twig',
                            'match' => [
                                'Identifier\ContentType' => [
                                    'foo',
                                ],
                            ],
                            'params' => [],
                        ],
                        'foo3' => [
                            'template' => '@templates/foo3.html.twig',
                            'match' => [
                                'Identifier\ContentType' => [
                                    'foo',
                                ],
                            ],
                            'params' => [
                                'valid_parameters' => ['param2'],
                            ],
                        ],
                    ],
                    'view_style_2' => [
                        'foo' => [
                            'template' => '@templates/foo.html.twig',
                            'match' => [
                                'Identifier\ContentType' => [
                                    'foo',
                                ],
                            ],
                            'params' => [],
                        ],
                    ],
                    'view_style_3' => [
                        'foo' => [
                            'template' => '@templates/foo.html.twig',
                            'match' => [
                                'Identifier\Section' => [
                                    'foo',
                                ],
                            ],
                            'params' => [
                                'valid_parameters' => ['param1'],
                            ],
                        ],
                    ],
                    'full' => [
                        'foo' => [
                            'template' => '@templates/foo.html.twig',
                            'match' => [
                                'Identifier\ContentType' => [
                                    'foo',
                                ],
                            ],
                            'params' => [],
                        ],
                    ],
                ],
            );

        $viewTypes = $this->configProvider->provideViewTypes($block);

        self::assertCount(2, $viewTypes);

        self::assertArrayHasKey('view_style_1', $viewTypes);
        self::assertArrayHasKey('view_style_2', $viewTypes);

        self::assertContainsOnlyInstancesOf(ViewType::class, $viewTypes);

        self::assertSame('view_style_1', $viewTypes['view_style_1']->identifier);
        self::assertSame('View Style 1', $viewTypes['view_style_1']->name);
        self::assertSame(['param1', 'param2'], $viewTypes['view_style_1']->validParameters);

        self::assertSame('view_style_2', $viewTypes['view_style_2']->identifier);
        self::assertSame('View Style 2', $viewTypes['view_style_2']->name);
        self::assertNull($viewTypes['view_style_2']->validParameters);
    }

    public function testProvideViewTypesWithoutBlock(): void
    {
        self::assertSame([], $this->configProvider->provideViewTypes());
    }
}
