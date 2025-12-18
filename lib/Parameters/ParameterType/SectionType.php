<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Parameters\ParameterType;

use Netgen\Layouts\Ibexa\Validator\Constraint as IbexaConstraints;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Parameters\ParameterType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

use function array_first;
use function is_array;

/**
 * Parameter type used to store and validate an identifier of a section in Ibexa CMS.
 */
final class SectionType extends ParameterType
{
    public static function getIdentifier(): string
    {
        return 'ibexa_section';
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->define('multiple')
            ->required()
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver
            ->define('sections')
            ->required()
            ->default([])
            ->allowedTypes('string[]');
    }

    /**
     * @return string[]|string|null
     */
    public function fromHash(ParameterDefinition $parameterDefinition, mixed $value): array|string|null
    {
        if ($value === null || $value === []) {
            return null;
        }

        if ($parameterDefinition->getOption('multiple') === true) {
            return is_array($value) ? $value : [$value];
        }

        return is_array($value) ? array_first($value) : $value;
    }

    public function isValueEmpty(ParameterDefinition $parameterDefinition, mixed $value): bool
    {
        return $value === null || $value === [];
    }

    protected function getValueConstraints(ParameterDefinition $parameterDefinition, mixed $value): array
    {
        $sectionConstraints = [
            new Constraints\Type(type: 'string'),
            new IbexaConstraints\Section(allowedSections: $parameterDefinition->getOption('sections')),
        ];

        if ($parameterDefinition->getOption('multiple') === false) {
            return $sectionConstraints;
        }

        return [
            new Constraints\Type(type: 'array'),
            new Constraints\All(
                constraints: $sectionConstraints,
            ),
        ];
    }
}
