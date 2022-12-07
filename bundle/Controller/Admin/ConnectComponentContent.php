<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Controller\Admin;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException as IbexaNotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Netgen\Layouts\API\Service\BlockService;
use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Exception\NotFoundException;
use Netgen\Layouts\Ibexa\Block\BlockDefinition\Handler\ComponentHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ConnectComponentContent extends Controller
{
    private BlockService $blockService;

    private ContentService $contentService;

    public function __construct(BlockService $blockService, ContentService $contentService)
    {
        $this->blockService = $blockService;
        $this->contentService = $contentService;
    }

    /**
     * Connects the provided content with the provided component block.
     */
    public function __invoke(Request $request, Block $block, int $contentId): Response
    {
        if (!$block->getDefinition()->getHandler() instanceof ComponentHandler) {
            throw new BadRequestHttpException();
        }

        try {
            $blockDraft = $this->blockService->loadBlockDraft($block->getId(), [$block->getLocale()]);
        } catch (NotFoundException $e) {
            throw new BadRequestHttpException();
        }

        try {
            $content = $this->contentService->loadContent($contentId);
        } catch (IbexaNotFoundException|UnauthorizedException $e) {
            throw new BadRequestHttpException();
        }

        $blockUpdateStruct = $this->blockService->newBlockUpdateStruct($blockDraft->getLocale(), $blockDraft);
        $blockUpdateStruct->setParameterValue('content', $content->id);

        $this->blockService->updateBlock($blockDraft, $blockUpdateStruct);

        return new Response();
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
