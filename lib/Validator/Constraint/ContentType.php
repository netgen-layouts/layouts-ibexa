<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class ContentType extends Constraint
{
    /**
     * @param array<string, mixed> $allowedTypes
     */
    #[HasNamedArguments]
    public function __construct(
        /**
         * If not empty, the constraint will validate only if content type identifier
         * is in the list of provided content type identifiers.
         */
        public array $allowedTypes = [],
        public string $message = 'netgen_layouts.ibexa.content_type.content_type_not_found',
        public string $notAllowedMessage = 'netgen_layouts.ibexa.content_type.content_type_not_allowed',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'nglayouts_ibexa_content_type';
    }
}
