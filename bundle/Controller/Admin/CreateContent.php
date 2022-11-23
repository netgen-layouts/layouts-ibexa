<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Controller\Admin;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateContent extends Controller
{
    private LocationService $locationService;

    private ContentTypeService $contentTypeService;

    public function __construct(
        LocationService $locationService,
        ContentTypeService $contentTypeService
    ) {
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Creates a content and redirects to route that edits the content.
     *
     * @param int|string $parentLocationId
     */
    public function __invoke(Request $request, string $contentTypeIdentifier, string $languageCode, $parentLocationId): Response
    {
        $location = $this->locationService->loadLocation((int) $parentLocationId);
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);

        return $this->redirectToRoute(
            'ibexa.content.create_no_draft',
            [
                'contentTypeIdentifier' => $contentType->identifier,
                'language' => $languageCode,
                'parentLocationId' => $location->id,
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
}