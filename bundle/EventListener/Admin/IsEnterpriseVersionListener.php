<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\EventListener\Admin;

use Netgen\Layouts\Event\BuildViewEvent;
use Netgen\Layouts\View\View\LayoutViewInterface;
use Netgen\Layouts\View\View\RuleViewInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class IsEnterpriseVersionListener implements EventSubscriberInterface
{
    public function __construct(
        private bool $isEnterpriseVersion,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BuildViewEvent::getEventName('layout') => 'onBuildView',
            BuildViewEvent::getEventName('rule') => 'onBuildView',
        ];
    }

    /**
     * Injects if Netgen Layouts is the enterprise version or not.
     */
    public function onBuildView(BuildViewEvent $event): void
    {
        if (!$event->view instanceof LayoutViewInterface && !$event->view instanceof RuleViewInterface) {
            return;
        }

        if ($event->view->context !== 'ibexa_admin') {
            return;
        }

        $event->view->addParameter('is_enterprise', $this->isEnterpriseVersion);
    }
}
