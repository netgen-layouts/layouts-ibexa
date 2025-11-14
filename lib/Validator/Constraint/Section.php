<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class Section extends Constraint
{
    /**
     * @param string[] $allowedSections
     */
    #[HasNamedArguments]
    public function __construct(
        /**
         * If not empty, the constraint will validate only if section identifier
         * is in the list of provided section identifiers.
         */
        public array $allowedSections = [],
        public string $message = 'netgen_layouts.ibexa.section.section_not_found',
        public string $notAllowedMessage = 'netgen_layouts.ibexa.section.section_not_allowed',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'nglayouts_ibexa_section';
    }
}
