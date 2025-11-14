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
 * Parameter type used to store and validate an identifier of a content type in Ibexa CMS.
 */
final class ContentTypeType extends ParameterType
{
    public static function getIdentifier(): string
    {
        return 'ibexa_content_type';
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->define('multiple')
            ->required()
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver
            ->define('types')
            ->required()
            ->default([])
            ->allowedTypes('array');
    }

    public function fromHash(ParameterDefinition $parameterDefinition, mixed $value): mixed
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
        $options = $parameterDefinition->getOptions();

        $contentTypeConstraints = [
            new Constraints\Type(type: 'string'),
            new IbexaConstraints\ContentType(allowedTypes: $parameterDefinition->getOption('types')),
        ];

        if ($options['multiple'] === false) {
            return $contentTypeConstraints;
        }

        return [
            new Constraints\Type(type: 'list'),
            new Constraints\All(
                constraints: $contentTypeConstraints,
            ),
        ];
    }
}
