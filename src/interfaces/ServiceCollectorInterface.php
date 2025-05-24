<?php

namespace Vengine\Libs\DI\interfaces;

use Vengine\Libs\DI\Container;

interface ServiceCollectorInterface
{
    public function collect(Container $container): void;
}
