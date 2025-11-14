<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class SiteAccess extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'netgen_layouts.ibexa.site_access.site_access_not_found',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'nglayouts_ibexa_site_access';
    }
}
