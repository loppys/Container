<?php

namespace Vengine\Libs\interfaces;

interface CollectorContainerInterface
{
    public function collect(ServiceCollectorInterface $collector): void;
}
