<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Tests\Templating;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\Bundle\LayoutsBundle\Templating\PageLayoutResolverInterface;
use Netgen\Bundle\LayoutsIbexaBundle\Templating\PageLayoutResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(PageLayoutResolver::class)]
final class PageLayoutResolverTest extends TestCase
{
    private Stub&PageLayoutResolverInterface $innerResolverStub;

    private Stub&ConfigResolverInterface $configResolverStub;

    private Stub&RequestStack $requestStackStub;

    private PageLayoutResolver $resolver;

    protected function setUp(): void
    {
        $this->innerResolverStub = self::createStub(PageLayoutResolverInterface::class);
        $this->configResolverStub = self::createStub(ConfigResolverInterface::class);
        $this->requestStackStub = self::createStub(RequestStack::class);

        $this->resolver = new PageLayoutResolver(
            $this->innerResolverStub,
            $this->configResolverStub,
            $this->requestStackStub,
            'fallback_layout.html.twig',
        );
    }

    public function testResolvePageLayout(): void
    {
        $request = Request::create('/');

        $this->requestStackStub
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->configResolverStub
            ->method('hasParameter')
            ->with(self::identicalTo('page_layout'))
            ->willReturn(true);

        $this->configResolverStub
            ->method('getParameter')
            ->with(self::identicalTo('page_layout'))
            ->willReturn('resolved_layout.html.twig');

        self::assertSame('resolved_layout.html.twig', $this->resolver->resolvePageLayout());
    }

    public function testResolvePageLayoutWitNoRequest(): void
    {
        $this->requestStackStub
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->innerResolverStub
            ->method('resolvePageLayout')
            ->willReturn('default_layout.html.twig');

        self::assertSame('default_layout.html.twig', $this->resolver->resolvePageLayout());
    }

    public function testResolvePageLayoutWithDisabledLayout(): void
    {
        $request = Request::create('/');
        $request->attributes->set('layout', false);

        $this->requestStackStub
            ->method('getCurrentRequest')
            ->willReturn($request);

        self::assertSame('fallback_layout.html.twig', $this->resolver->resolvePageLayout());
    }
}
