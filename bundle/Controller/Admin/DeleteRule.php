<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Controller\Admin;

use Netgen\Layouts\API\Service\LayoutResolverService;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\API\Values\LayoutResolver\Rule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteRule extends Controller
{
    public function __construct(
        private LayoutService $layoutService,
        private LayoutResolverService $layoutResolverService,
    ) {}

    /**
     * Deletes the provided rule.
     */
    public function __invoke(Rule $rule, Request $request): Response
    {
        if (!$this->isGranted('ROLE_NGLAYOUTS_ADMIN')) {
            $this->denyAccessUnlessGranted(
                'nglayouts:mapping:delete',
                ['ruleGroup', $rule->ruleGroupId->toString()],
            );
        }

        if (
            $rule->layout !== null
            && $this->layoutResolverService->getRuleCountForLayout($rule->layout) === 1
            && ($this->isGranted('ROLE_NGLAYOUTS_ADMIN') || $this->isGranted('nglayouts:layout:delete'))
        ) {
            $this->layoutService->deleteLayout($rule->layout);
        }

        $this->layoutResolverService->deleteRule($rule);

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    public function checkPermissions(): void {}
}
