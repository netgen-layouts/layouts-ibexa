<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Collection\QueryType\Handler\Traits;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Ibexa\ContentProvider\ContentProviderInterface;
use Netgen\Layouts\Ibexa\Parameters\ParameterType as IbexaParameterType;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterCollectionInterface;
use Netgen\Layouts\Parameters\ParameterType;
use Throwable;

trait ParentLocationTrait
{
    private ContentProviderInterface $contentProvider;

    private LocationService $locationService;

    /**
     * Builds the parameters for filtering by parent location.
     *
     * @param string[] $groups
     */
    private function buildParentLocationParameters(ParameterBuilderInterface $builder, array $groups = []): void
    {
        $builder->add(
            'use_current_location',
            ParameterType\Compound\BooleanType::class,
            [
                'reverse' => true,
                'groups' => $groups,
            ],
        );

        $builder->get('use_current_location')->add(
            'parent_location_id',
            IbexaParameterType\LocationType::class,
            [
                'allow_invalid' => true,
                'groups' => $groups,
            ],
        );
    }

    /**
     * Returns the parent location to use for the parameter collection.
     */
    private function getParentLocation(ParameterCollectionInterface $parameterCollection): ?Location
    {
        if ($parameterCollection->getParameter('use_current_location')->value === true) {
            return $this->contentProvider->provideLocation();
        }

        $parentLocationId = $parameterCollection->getParameter('parent_location_id')->value;
        if ($parentLocationId === null) {
            return null;
        }

        try {
            $parentLocation = $this->locationService->loadLocation((int) $parentLocationId);

            return $parentLocation->invisible ? null : $parentLocation;
        } catch (Throwable) {
            return null;
        }
    }
}
