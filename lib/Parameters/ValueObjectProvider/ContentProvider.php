<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Parameters\ValueObjectProvider;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Netgen\Layouts\Error\ErrorHandlerInterface;
use Netgen\Layouts\Parameters\ValueObjectProviderInterface;

final class ContentProvider implements ValueObjectProviderInterface
{
    public function __construct(private Repository $repository, private ErrorHandlerInterface $errorHandler)
    {
    }

    public function getValueObject(mixed $value): ?Content
    {
        try {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
            $content = $this->repository->sudo(
                fn (): Content => $this->repository->getContentService()->loadContent((int) $value),
            );

            return $content->contentInfo->mainLocationId !== null ? $content : null;
        } catch (NotFoundException $e) {
            $this->errorHandler->handleError($e);

            return null;
        }
    }
}
