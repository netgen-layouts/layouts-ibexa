<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\LayoutsBundle\DependencyInjection\NetgenLayoutsExtension;
use Netgen\Bundle\LayoutsIbexaBundle\DependencyInjection\ExtensionPlugin;
use Netgen\Bundle\LayoutsIbexaBundle\DependencyInjection\NetgenLayoutsIbexaExtension;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_merge;

#[CoversClass(ExtensionPlugin::class)]
final class ExtensionPluginTest extends AbstractExtensionTestCase
{
    public function testAppendFromPlugin(): void
    {
        $extension = new NetgenLayoutsExtension();
        $extension->addPlugin(new ExtensionPlugin($this->container, $extension));

        $extension->prepend($this->container);

        $config = array_merge(
            ...$this->container->getExtensionConfig('netgen_layouts'),
        );

        self::assertArrayHasKey('block_type_groups', $config);
        self::assertArrayHasKey('placeholders', $config['block_type_groups']);

        self::assertSame(
            [
                'block_types' => [
                    'ibexa_content_field',
                ],
            ],
            $config['block_type_groups']['placeholders'],
        );
    }

    protected function getContainerExtensions(): array
    {
        return [new NetgenLayoutsIbexaExtension()];
    }
}
