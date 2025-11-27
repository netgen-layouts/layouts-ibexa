<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\EventListener\Admin;

use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Netgen\Bundle\LayoutsAdminBundle\Event\AdminMatchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use function in_array;

final class SetPageLayoutListener implements EventSubscriberInterface
{
    /**
     * @param array<string, string[]> $groupsBySiteAccess
     */
    public function __construct(
        private RequestStack $requestStack,
        private array $groupsBySiteAccess,
        private string $pageLayoutTemplate,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [AdminMatchEvent::class => ['onAdminMatch', -255]];
    }

    /**
     * Sets the pagelayout template for admin interface.
     */
    public function onAdminMatch(AdminMatchEvent $event): void
    {
        if ($event->pageLayoutTemplate !== null) {
            return;
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest instanceof Request) {
            return;
        }

        $siteAccess = $currentRequest->attributes->get('siteaccess')->name;
        if (!in_array(IbexaAdminUiBundle::ADMIN_GROUP_NAME, $this->groupsBySiteAccess[$siteAccess] ?? [], true)) {
            return;
        }

        $event->pageLayoutTemplate = $this->pageLayoutTemplate;
    }
}
