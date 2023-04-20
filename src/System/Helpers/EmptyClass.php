<?php

namespace Loader\System\Helpers;

use Loader\System\Interfaces\ContainerInjection;

class EmptyClass implements ContainerInjection
{
    public static function getName(): string
    {
        return 'empty';
    }
}