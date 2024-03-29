<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Layout\Resolver\ConditionType;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Netgen\Layouts\Ibexa\ContentProvider\ContentExtractorInterface;
use Netgen\Layouts\Ibexa\Validator\Constraint as IbexaConstraints;
use Netgen\Layouts\Layout\Resolver\ConditionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

use function count;
use function in_array;
use function is_array;

final class ContentType extends ConditionType
{
    public function __construct(private ContentExtractorInterface $contentExtractor) {}

    public static function getType(): string
    {
        return 'ibexa_content_type';
    }

    public function getConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
            new Constraints\Type(['type' => 'array']),
            new Constraints\All(
                [
                    'constraints' => [
                        new Constraints\Type(['type' => 'string']),
                        new IbexaConstraints\ContentType(),
                    ],
                ],
            ),
        ];
    }

    public function matches(Request $request, mixed $value): bool
    {
        if (!is_array($value) || count($value) === 0) {
            return false;
        }

        $content = $this->contentExtractor->extractContent($request);
        if (!$content instanceof Content) {
            return false;
        }

        return in_array($content->getContentType()->identifier, $value, true);
    }
}
