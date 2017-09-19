<?php

namespace Netgen\Bundle\EzPublishBlockManagerBundle\DependencyInjection\CompilerPass\View;

use Netgen\Bundle\BlockManagerBundle\DependencyInjection\CompilerPass\View\DefaultViewTemplatesPass as BasePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultViewTemplatesPass extends BasePass
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('ezpublish.siteaccess.list')) {
            return;
        }

        $scopes = array_merge(
            array('default'),
            $container->getParameter('ezpublish.siteaccess.list')
        );

        foreach ($scopes as $scope) {
            $scopeParam = "netgen_block_manager.{$scope}.view";
            if (!$container->hasParameter($scopeParam)) {
                continue;
            }

            $scopeRules = $container->getParameter($scopeParam);
            $scopeRules = $this->updateRules($container, $scopeRules);
            $container->setParameter($scopeParam, $scopeRules);
        }
    }
}
