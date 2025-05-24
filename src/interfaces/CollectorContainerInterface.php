<?php

namespace Vengine\Libs\DI\interfaces;

interface CollectorContainerInterface
{
    public function collect(ServiceCollectorInterface $collector): void;
}
