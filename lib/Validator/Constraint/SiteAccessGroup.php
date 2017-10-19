<?php

namespace Netgen\BlockManager\Ez\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class SiteAccessGroup extends Constraint
{
    /**
     * @var string
     */
    public $message = 'netgen_block_manager.ez_site_access_group.site_access_group_not_found';

    public function validatedBy()
    {
        return 'ngbm_ez_site_access_group';
    }
}