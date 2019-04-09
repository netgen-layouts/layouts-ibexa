<?php

declare(strict_types=1);

namespace Netgen\Bundle\EzPublishBlockManagerBundle\Templating\Twig\Runtime;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Throwable;

final class EzPlatformRuntime
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns the content name.
     *
     * @param int|string $contentId
     *
     * @return string
     */
    public function getContentName($contentId): string
    {
        try {
            $versionInfo = $this->loadVersionInfo($contentId);

            return $versionInfo->getName() ?? '';
        } catch (Throwable $t) {
            return '';
        }
    }

    /**
     * Returns the location path.
     *
     * @param int|string $locationId
     *
     * @return string[]
     */
    public function getLocationPath($locationId): array
    {
        try {
            $location = $this->loadLocation($locationId);

            $locationPath = $location->path;
            array_shift($locationPath);

            $translatedNames = [];

            foreach ($locationPath as $locationPathId) {
                $locationInPath = $this->loadLocation($locationPathId);
                $translatedNames[] = $this->getContentName($locationInPath->contentInfo->id);
            }

            return $translatedNames;
        } catch (Throwable $t) {
            return [];
        }
    }

    /**
     * Returns the content type name.
     */
    public function getContentTypeName(string $identifier): string
    {
        try {
            $contentType = $this->loadContentType($identifier);

            return $contentType->getName() ?? '';
        } catch (Throwable $t) {
            return '';
        }
    }

    /**
     * Loads the version info for provided content ID.
     *
     * @param int|string $contentId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    private function loadVersionInfo($contentId): VersionInfo
    {
        return $this->repository->sudo(
            static function (Repository $repository) use ($contentId): VersionInfo {
                return $repository->getContentService()->loadVersionInfoById($contentId);
            }
        );
    }

    /**
     * Loads the location for provided location ID.
     *
     * @param int|string $locationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    private function loadLocation($locationId): Location
    {
        return $this->repository->sudo(
            static function (Repository $repository) use ($locationId): Location {
                return $repository->getLocationService()->loadLocation($locationId);
            }
        );
    }

    /**
     * Loads the content type for provided identifier.
     */
    private function loadContentType(string $identifier): ContentType
    {
        return $this->repository->sudo(
            static function (Repository $repository) use ($identifier): ContentType {
                return $repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);
            }
        );
    }
}