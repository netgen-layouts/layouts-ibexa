<?php

namespace Netgen\BlockManager\Ez\Parameters\ParameterType;

use Netgen\BlockManager\Ez\Validator\Constraint as EzConstraints;
use Netgen\BlockManager\Parameters\ParameterDefinition;
use Netgen\BlockManager\Parameters\ParameterType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Parameter type used to store and validate an identifier of a object state in eZ Platform.
 */
final class ObjectStateType extends ParameterType
{
    public function getIdentifier()
    {
        return 'ez_object_state';
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefault('multiple', false);
        $optionsResolver->setDefault('states', []);

        $optionsResolver->setRequired(['multiple', 'states']);

        $optionsResolver->setAllowedTypes('multiple', 'bool');
        $optionsResolver->setAllowedTypes('states', 'array');
    }

    public function fromHash(ParameterDefinition $parameterDefinition, $value)
    {
        if ($value === null || $value === []) {
            return null;
        }

        if ($parameterDefinition->getOption('multiple')) {
            return is_array($value) ? $value : [$value];
        }

        return is_array($value) ? array_values($value)[0] : $value;
    }

    public function isValueEmpty(ParameterDefinition $parameterDefinition, $value)
    {
        return $value === null || $value === [];
    }

    protected function getValueConstraints(ParameterDefinition $parameterDefinition, $value)
    {
        $options = $parameterDefinition->getOptions();

        $objectStateConstraints = [
            new Constraints\Type(['type' => 'string']),
            new EzConstraints\ObjectState(['allowedStates' => $parameterDefinition->getOption('states')]),
        ];

        if (!$options['multiple']) {
            return $objectStateConstraints;
        }

        return [
            new Constraints\Type(['type' => 'array']),
            new Constraints\All(
                [
                    'constraints' => $objectStateConstraints,
                ]
            ),
        ];
    }
}
