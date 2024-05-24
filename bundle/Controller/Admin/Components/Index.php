<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Controller\Admin\Components;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use Netgen\Bundle\LayoutsIbexaBundle\Controller\Admin\Controller;
use Netgen\Layouts\Ibexa\AdminUI\ComponentLayoutsLoader;
use Netgen\Layouts\Ibexa\Form\ComponentFilterType;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_keys;
use function array_values;
use function ksort;
use function max;

final class Index extends Controller
{
    public function __construct(
        private ComponentLayoutsLoader $componentLayoutsLoader,
        private ConfigResolverInterface $configResolver,
        private SearchService $searchService,
        private int $limit,
    ) {}

    /**
     * Shows the list of all components and related layouts.
     */
    public function __invoke(Request $request): Response
    {
        $filterForm = $this->createForm(ComponentFilterType::class);
        $filterForm->handleRequest($request);

        $pager = $this->buildPager(
            new LocationSearchAdapter(
                $this->getComponentsQuery($filterForm),
                $this->searchService,
            ),
            $request,
        );

        return $this->render(
            '@NetgenLayoutsIbexa/admin/components/index.html.twig',
            [
                'components' => $this->extractComponents($pager),
                'component_layouts' => $this->componentLayoutsLoader->loadLayoutsData(),
                'filter_form' => $filterForm->createView(),
                'pager' => $pager,
            ],
        );
    }

    public function checkPermissions(): void
    {
        if ($this->isGranted('ROLE_NGLAYOUTS_EDITOR')) {
            return;
        }

        if ($this->isGranted('nglayouts:ui:access')) {
            return;
        }

        $exception = $this->createAccessDeniedException();
        $exception->setAttributes('nglayouts:ui:access');

        throw $exception;
    }

    private function getComponentsQuery(FormInterface $form): LocationQuery
    {
        $parentLocationsConfig = $this->configResolver->getParameter(
            'ibexa_component.parent_locations',
            'netgen_layouts',
        );

        $locationQuery = new LocationQuery();
        $locationQuery->filter = new Criterion\LogicalAnd(
            [
                new Criterion\ParentLocationId(array_values($parentLocationsConfig)),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
                new Criterion\ContentTypeIdentifier(
                    $form->get('contentType')->getData() ?? array_keys($parentLocationsConfig),
                ),
            ],
        );

        $locationQuery->sortClauses = [
            new SortClause\ContentName(Query::SORT_ASC),
        ];

        return $locationQuery;
    }

    /**
     * @return array<string, \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo[]>
     */
    private function extractComponents(PagerfantaInterface $pager): array
    {
        $components = [];

        foreach ($pager->getCurrentPageResults() as $location) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $contentTypeName = $location->getContentInfo()->getContentType()->getName();
            $components[$contentTypeName][] = $location->getContentInfo();
        }

        ksort($components);

        return $components;
    }

    private function buildPager(AdapterInterface $adapter, Request $request): PagerfantaInterface
    {
        $pager = new Pagerfanta($adapter);

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($this->limit);
        $pager->setCurrentPage(max(1, $request->query->getInt('page', 1)));

        return $pager;
    }
}
