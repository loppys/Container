<?php

namespace Vengine\Libs\interfaces;

use Vengine\Libs\Container;

interface ServiceCollectorInterface
{
    public function collect(Container $container): void;
}
