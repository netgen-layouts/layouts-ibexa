<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class ObjectState extends Constraint
{
    /**
     * @param array<string, mixed> $allowedStates
     */
    #[HasNamedArguments]
    public function __construct(
        /**
         * If not empty, the constraint will validate only if object state identifier
         * is in the list of provided object state identifiers.
         */
        public array $allowedStates = [],
        public string $message = 'netgen_layouts.ibexa.object_state.object_state_not_found',
        public string $invalidGroupMessage = 'netgen_layouts.ibexa.object_state.object_state_group_not_found',
        public string $notAllowedMessage = 'netgen_layouts.ibexa.object_state.object_state_not_allowed',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'nglayouts_ibexa_object_state';
    }
}
