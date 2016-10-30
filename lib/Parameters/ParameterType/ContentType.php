<?php

namespace Netgen\BlockManager\Ez\Parameters\ParameterType;

use Netgen\BlockManager\Parameters\ParameterInterface;
use Netgen\BlockManager\Parameters\ParameterType;
use Symfony\Component\Validator\Constraints;
use Netgen\BlockManager\Ez\Validator\Constraint as EzConstraints;

class ContentType extends ParameterType
{
    /**
     * Returns the parameter type.
     *
     * @return string
     */
    public function getType()
    {
        return 'ez_content_type';
    }

    /**
     * Returns constraints that will be used to validate the parameter value.
     *
     * @param \Netgen\BlockManager\Parameters\ParameterInterface $parameter
     * @param mixed $value
     *
     * @return \Symfony\Component\Validator\Constraint[]
     */
    public function getValueConstraints(ParameterInterface $parameter, $value)
    {
        $options = $parameter->getOptions();

        $contentTypeConstraints = array(
            new Constraints\Type(array('type' => 'string')),
            new EzConstraints\ContentType(),
        );

        if (!$options['multiple']) {
            return $contentTypeConstraints;
        }

        return array(
            new Constraints\Type(array('type' => 'array')),
            new Constraints\All(
                array(
                    'constraints' => $contentTypeConstraints,
                )
            ),
        );
    }
}
