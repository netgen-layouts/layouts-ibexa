<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\EventListener\Admin;

use Netgen\Layouts\Event\BuildViewEvent;
use Netgen\Layouts\HttpCache\ClientInterface;
use Netgen\Layouts\HttpCache\NullClient;
use Netgen\Layouts\View\View\LayoutViewInterface;
use Netgen\Layouts\View\View\RuleViewInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CacheEnabledListener implements EventSubscriberInterface
{
    public function __construct(
        private ClientInterface $httpCacheClient,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BuildViewEvent::getEventName('layout') => 'onBuildView',
            BuildViewEvent::getEventName('rule') => 'onBuildView',
        ];
    }

    /**
     * Injects if the HTTP cache clearing is enabled or not.
     */
    public function onBuildView(BuildViewEvent $event): void
    {
        if (!$event->view instanceof LayoutViewInterface && !$event->view instanceof RuleViewInterface) {
            return;
        }

        if ($event->view->context !== 'ibexa_admin') {
            return;
        }

        $event->view->addParameter(
            'http_cache_enabled',
            !$this->httpCacheClient instanceof NullClient,
        );
    }
}
