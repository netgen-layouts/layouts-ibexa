<?php

namespace Netgen\BlockManager\Ez\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\BlockManager\Layout\Resolver\Form\TargetType\Mapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SemanticPathInfo extends Mapper
{
    public function getFormType()
    {
        return TextType::class;
    }
}
