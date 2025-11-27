<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\EventListener\Admin;

use Netgen\Layouts\Event\CollectViewParametersEvent;
use Netgen\Layouts\Event\LayoutsEvents;
use Netgen\Layouts\View\View\LayoutViewInterface;
use Netgen\Layouts\View\View\RuleViewInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function sprintf;

final class IsEnterpriseVersionListener implements EventSubscriberInterface
{
    public function __construct(
        private bool $isEnterpriseVersion,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            sprintf('%s.%s', LayoutsEvents::BUILD_VIEW, 'layout') => 'onBuildView',
            sprintf('%s.%s', LayoutsEvents::BUILD_VIEW, 'rule') => 'onBuildView',
        ];
    }

    /**
     * Injects if Netgen Layouts is the enterprise version or not.
     */
    public function onBuildView(CollectViewParametersEvent $event): void
    {
        if (!$event->view instanceof LayoutViewInterface && !$event->view instanceof RuleViewInterface) {
            return;
        }

        if ($event->view->getContext() !== 'ibexa_admin') {
            return;
        }

        $event->addParameter('is_enterprise', $this->isEnterpriseVersion);
    }
}
