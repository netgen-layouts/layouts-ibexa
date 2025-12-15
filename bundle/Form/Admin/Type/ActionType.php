<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\Form\Admin\Type;

enum ActionType: string
{
    case NewLayout = 'new_layout';
    case CopyLayout = 'copy_layout';
}
