<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Tests\Configuration;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\Bundle\LayoutsBundle\Configuration\ConfigurationInterface;
use Netgen\Bundle\LayoutsBundle\Exception\ConfigurationException;
use Netgen\Bundle\LayoutsIbexaBundle\Configuration\ConfigResolverConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigResolverConfiguration::class)]
final class ConfigResolverConfigurationTest extends TestCase
{
    private Stub&ConfigResolverInterface $configResolverStub;

    private Stub&ConfigurationInterface $fallbackConfigurationStub;

    private ConfigResolverConfiguration $configuration;

    protected function setUp(): void
    {
        $this->configResolverStub = self::createStub(ConfigResolverInterface::class);
        $this->fallbackConfigurationStub = self::createStub(ConfigurationInterface::class);

        $this->configuration = new ConfigResolverConfiguration(
            $this->configResolverStub,
            $this->fallbackConfigurationStub,
        );
    }

    public function testHasParameter(): void
    {
        $this->configResolverStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'), self::identicalTo('netgen_layouts'))
            ->willReturn(true);

        self::assertTrue($this->configuration->hasParameter('some_param'));
    }

    public function testHasParameterWithNoParameter(): void
    {
        $this->configResolverStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'), self::identicalTo('netgen_layouts'))
            ->willReturn(false);

        $this->fallbackConfigurationStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'))
            ->willReturn(true);

        self::assertTrue($this->configuration->hasParameter('some_param'));
    }

    public function testHasParameterWithNoFallbackParameter(): void
    {
        $this->configResolverStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'), self::identicalTo('netgen_layouts'))
            ->willReturn(false);

        $this->fallbackConfigurationStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'))
            ->willReturn(false);

        self::assertFalse($this->configuration->hasParameter('some_param'));
    }

    public function testGetParameter(): void
    {
        $this->configResolverStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'), self::identicalTo('netgen_layouts'))
            ->willReturn(true);

        $this->configResolverStub
            ->method('getParameter')
            ->with(self::identicalTo('some_param'), self::identicalTo('netgen_layouts'))
            ->willReturn('some_param_value');

        self::assertSame('some_param_value', $this->configuration->getParameter('some_param'));
    }

    public function testGetFallbackParameter(): void
    {
        $this->configResolverStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'), self::identicalTo('netgen_layouts'))
            ->willReturn(false);

        $this->fallbackConfigurationStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'))
            ->willReturn(true);

        $this->fallbackConfigurationStub
            ->method('getParameter')
            ->with(self::identicalTo('some_param'))
            ->willReturn('some_param_value');

        self::assertSame('some_param_value', $this->configuration->getParameter('some_param'));
    }

    public function testGetParameterThrowsConfigurationException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Parameter "some_param" does not exist in configuration.');

        $this->configResolverStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'), self::identicalTo('netgen_layouts'))
            ->willReturn(false);

        $this->fallbackConfigurationStub
            ->method('hasParameter')
            ->with(self::identicalTo('some_param'))
            ->willReturn(false);

        $this->configuration->getParameter('some_param');
    }
}
