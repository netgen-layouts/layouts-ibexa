<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Utils;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

final class RemoteIdConverter
{
    public function __construct(private Repository $repository) {}

    public function toLocationId(string $remoteId): ?int
    {
        try {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $location = $this->repository->sudo(
                fn (): Location => $this->repository->getLocationService()->loadLocationByRemoteId($remoteId),
            );

            return (int) $location->id;
        } catch (NotFoundException) {
            return null;
        }
    }

    public function toLocationRemoteId(int $id): ?string
    {
        try {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $location = $this->repository->sudo(
                fn (): Location => $this->repository->getLocationService()->loadLocation($id),
            );

            return $location->remoteId;
        } catch (NotFoundException) {
            return null;
        }
    }

    public function toContentId(string $remoteId): ?int
    {
        try {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo */
            $contentInfo = $this->repository->sudo(
                fn (): ContentInfo => $this->repository->getContentService()->loadContentInfoByRemoteId($remoteId),
            );

            return $contentInfo->id;
        } catch (NotFoundException) {
            return null;
        }
    }

    public function toContentRemoteId(int $id): ?string
    {
        try {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo */
            $contentInfo = $this->repository->sudo(
                fn (): ContentInfo => $this->repository->getContentService()->loadContentInfo($id),
            );

            return $contentInfo->remoteId;
        } catch (NotFoundException) {
            return null;
        }
    }
}
