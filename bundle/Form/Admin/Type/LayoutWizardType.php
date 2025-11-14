<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Form\Admin\Type;

use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\Layout\Registry\LayoutTypeRegistry;
use Netgen\Layouts\Validator\Constraint\LayoutName;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

use function array_first;

final class LayoutWizardType extends AbstractType
{
    public const string ACTION_TYPE_NEW_LAYOUT = 'new_layout';

    public const string ACTION_TYPE_COPY_LAYOUT = 'copy_layout';

    public function __construct(
        private LayoutService $layoutService,
        private LayoutTypeRegistry $layoutTypeRegistry,
        private bool $isEnterprise,
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'nglayouts_ibexa_admin_forms');

        $resolver->setDefault(
            'validation_groups',
            static fn (FormInterface $form): array => [
                'Default',
                $form->get('action')->getData(),
            ],
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $layoutTypes = $this->layoutTypeRegistry->getLayoutTypes(true);

        $builder->add(
            'action',
            Type\ChoiceType::class,
            [
                'label' => false,
                'expanded' => true,
                'data' => self::ACTION_TYPE_NEW_LAYOUT,
                'choices' => [
                    'layout_wizard.action.new_layout' => self::ACTION_TYPE_NEW_LAYOUT,
                    'layout_wizard.action.copy_layout' => self::ACTION_TYPE_COPY_LAYOUT,
                ],
            ],
        );

        $builder->add(
            'layout_type',
            Type\ChoiceType::class,
            [
                'label' => 'layout_wizard.layout_type',
                'required' => true,
                'choices' => $layoutTypes,
                'choice_value' => 'identifier',
                'choice_name' => 'identifier',
                'choice_label' => 'name',
                'choice_translation_domain' => false,
                'expanded' => true,
                'data' => array_first($layoutTypes),
                'constraints' => [
                    new Constraints\NotBlank(groups: [self::ACTION_TYPE_NEW_LAYOUT]),
                ],
            ],
        );

        $builder->add(
            'layout',
            Type\ChoiceType::class,
            [
                'label' => 'layout_wizard.layout',
                'choices' => $this->layoutService->loadAllLayouts()->filter(
                    static fn (Layout $layout): bool => !$layout->isShared(),
                ),
                'choice_value' => 'id',
                'choice_label' => 'name',
            ],
        );

        $builder->add(
            'layout_name',
            Type\TextType::class,
            [
                'label' => 'layout_wizard.layout_name',
                'constraints' => [
                    new Constraints\NotBlank(),
                    new LayoutName(),
                ],
            ],
        );

        $builder->add(
            'layout_description',
            Type\TextareaType::class,
            [
                'label' => 'layout_wizard.layout_description',
                'required' => false,
                'constraints' => [
                    new Constraints\NotNull(),
                    new Constraints\Type(type: 'string'),
                ],
                'empty_data' => '',
            ],
        );

        if ($this->isEnterprise) {
            $builder->add(
                'rule_group',
                Type\HiddenType::class,
                [
                    'label' => 'layout_wizard.rule_group',
                    'error_bubbling' => false,
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\AtLeastOneOf(
                            constraints: [
                                new Constraints\EqualTo(Uuid::NIL),
                                new Constraints\Uuid(),
                            ],
                        ),
                    ],
                ],
            );
        }

        $builder->add(
            'activate_rule',
            Type\CheckboxType::class,
            [
                'label' => 'layout_wizard.activate_rule',
                'data' => true,
                'constraints' => [
                    new Constraints\NotNull(),
                    new Constraints\Type(type: 'bool'),
                ],
            ],
        );
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($this->layoutTypeRegistry->getLayoutTypes(true) as $layoutType) {
            $formView = $view['layout_type'][$layoutType->getIdentifier()] ?? null;

            if ($formView instanceof FormView) {
                $formView->vars['layout_type'] = $layoutType;
            }
        }
    }
}
