<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class Content extends Constraint
{
    /**
     * @param string[] $allowedTypes
     */
    #[HasNamedArguments]
    public function __construct(
        /**
         * If not empty, the constraint will only accept content with provided content types.
         */
        public array $allowedTypes = [],
        /**
         * If set to true, the constraint will accept values for non existing content.
         */
        public bool $allowInvalid = false,
        public string $message = 'netgen_layouts.ibexa.content.content_not_found',
        public string $typeNotAllowedMessage = 'netgen_layouts.ibexa.content.type_not_allowed',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'nglayouts_ibexa_content';
    }
}
