<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\DependencyInjection\CompilerPass\View;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function is_array;
use function sprintf;

final class DefaultViewTemplatesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('ibexa.site_access.list')) {
            return;
        }

        /** @var string[] $siteAccessList */
        $siteAccessList = $container->getParameter('ibexa.site_access.list');
        $scopes = [...['default'], ...$siteAccessList];

        foreach ($scopes as $scope) {
            $scopeParam = sprintf('netgen_layouts.%s.view', $scope);
            if (!$container->hasParameter($scopeParam)) {
                continue;
            }

            /** @var array<string, mixed[]>|null $scopeRules */
            $scopeRules = $container->getParameter($scopeParam);
            $scopeRules = $this->updateRules($container, $scopeRules);
            $container->setParameter($scopeParam, $scopeRules);
        }
    }

    /**
     * Updates all view rules to add the default template match.
     *
     * @param mixed[]|null $allRules
     *
     * @return mixed[]
     */
    private function updateRules(ContainerBuilder $container, ?array $allRules): array
    {
        $allRules ??= [];

        /** @var array<string, mixed[]> $defaultTemplates */
        $defaultTemplates = $container->getParameter('netgen_layouts.default_view_templates');

        foreach ($defaultTemplates as $viewName => $viewTemplates) {
            foreach ($viewTemplates as $context => $template) {
                $rules = [];

                if (is_array($allRules[$viewName][$context] ?? null)) {
                    $rules = $allRules[$viewName][$context];
                }

                $rules = $this->addDefaultRule($viewName, $context, $rules, $template);

                $allRules[$viewName][$context] = $rules;
            }
        }

        return $allRules;
    }

    /**
     * Adds the default view template as a fallback to specified view rules.
     *
     * @param mixed[] $rules
     *
     * @return mixed[]
     */
    private function addDefaultRule(string $viewName, string $context, array $rules, string $defaultTemplate): array
    {
        return $rules + [
            sprintf('___%s_%s_default___', $viewName, $context) => [
                'template' => $defaultTemplate,
                'match' => [],
                'parameters' => [],
            ],
        ];
    }
}
