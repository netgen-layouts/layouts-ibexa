<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\AdminUI\Tab\LocationView;

use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\PermissionService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

final class LayoutsTab implements TabInterface, ConditionalTabInterface
{
    public function __construct(
        private Environment $twig,
        private PermissionService $permissionService,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {}

    public function getIdentifier(): string
    {
        return 'netgen_layouts';
    }

    public function getName(): string
    {
        return 'Netgen Layouts';
    }

    public function evaluate(array $parameters): bool
    {
        try {
            return $this->permissionService->hasAccess('nglayouts', 'editor') !== false;
        } catch (InvalidArgumentException) {
            // If nglayouts/editor permission does not exist (e.g. when using Netgen Layouts Enterprise)
            return $this->authorizationChecker->isGranted('nglayouts:ui:access');
        }
    }

    public function renderView(array $parameters): string
    {
        return $this->twig->render(
            '@ibexadesign/content/tab/nglayouts/tab.html.twig',
            $parameters,
        );
    }
}
