<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Form;

use Generator;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_keys;
use function ksort;

final class ComponentFilterType extends AbstractType
{
    public function __construct(
        private ConfigResolverInterface $configResolver,
        private ContentTypeService $contentTypeService,
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'nglayouts_admin');
        $resolver->setDefault('method', Request::METHOD_GET);
        $resolver->setDefault('csrf_protection', false);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $parentLocationsConfig = $this->configResolver->getParameter(
            'ibexa_component.parent_locations',
            'netgen_layouts',
        );

        ksort($parentLocationsConfig);

        $builder->add(
            'contentType',
            ChoiceType::class,
            [
                'required' => false,
                'label' => 'components.filter_form.content_type.label',
                'choices' => (function () use ($parentLocationsConfig): Generator {
                    foreach (array_keys($parentLocationsConfig) as $contentTypeIdentifier) {
                        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);

                        yield $contentType->getName() => $contentType->identifier;
                    }
                })(),
            ],
        );

        $builder->add(
            'showOnlyUnused',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'components.filter_form.show_only_unused.label',
            ],
        );

        $builder->add(
            'sortType',
            ChoiceType::class,
            [
                'required' => true,
                'label' => 'components.filter_form.sort_type.label',
                'choices' => [
                    'components.filter_form.sort_type.name' => 'name',
                    'components.filter_form.sort_type.last_modified' => 'last_modified',
                ],
            ],
        );

        $builder->add(
            'sortDirection',
            ChoiceType::class,
            [
                'required' => true,
                'label' => 'components.filter_form.sort_direction.label',
                'choices' => [
                    'components.filter_form.sort_direction.ascending' => 'ascending',
                    'components.filter_form.sort_direction.descending' => 'descending',
                ],
            ],
        );
    }
}
