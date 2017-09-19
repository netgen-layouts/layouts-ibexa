<?php

namespace Netgen\BlockManager\Ez\Layout\Resolver\ConditionType;

use eZ\Publish\Core\MVC\Symfony\SiteAccess as EzPublishSiteAccess;
use Netgen\BlockManager\Ez\Validator\Constraint as EzConstraints;
use Netgen\BlockManager\Layout\Resolver\ConditionTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

class SiteAccess implements ConditionTypeInterface
{
    public function getType()
    {
        return 'ez_site_access';
    }

    public function getConstraints()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type(array('type' => 'array')),
            new Constraints\All(
                array(
                    'constraints' => array(
                        new Constraints\Type(array('type' => 'string')),
                        new EzConstraints\SiteAccess(),
                    ),
                )
            ),
        );
    }

    public function matches(Request $request, $value)
    {
        $siteAccess = $request->attributes->get('siteaccess');
        if (!$siteAccess instanceof EzPublishSiteAccess) {
            return false;
        }

        if (!is_array($value) || empty($value)) {
            return false;
        }

        return in_array($siteAccess->name, $value, true);
    }
}
