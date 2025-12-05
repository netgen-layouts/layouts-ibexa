<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Form;

use Ibexa\Contracts\Core\Repository\SectionService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function count;
use function in_array;

final class SectionType extends AbstractType
{
    public function __construct(
        private SectionService $sectionService,
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'nglayouts_forms');

        $resolver->setDefault(
            'choices',
            fn (Options $options): array => $this->getSections($options['sections']),
        );

        $resolver->setDefault('choice_translation_domain', false);

        $resolver
            ->define('sections')
            ->required()
            ->default([])
            ->allowedTypes('string[]');
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * Returns the allowed sections from Ibexa CMS.
     *
     * @param array<string, string[]> $configuredSections
     *
     * @return array<string, string>
     */
    private function getSections(array $configuredSections): array
    {
        $allSections = [];

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Section[] $sections */
        $sections = $this->sectionService->loadSections();

        foreach ($sections as $section) {
            if (count($configuredSections) > 0 && !in_array($section->identifier, $configuredSections, true)) {
                continue;
            }

            $allSections[$section->name] = $section->identifier;
        }

        return $allSections;
    }
}
