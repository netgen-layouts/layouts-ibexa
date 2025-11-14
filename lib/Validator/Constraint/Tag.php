<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class Tag extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        /**
         * If set to true, the constraint will accept values for non existing tags.
         */
        public bool $allowInvalid = false,
        public string $message = 'netgen_layouts.ibexa.tags.tag_not_found',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'nglayouts_netgen_tags';
    }
}
