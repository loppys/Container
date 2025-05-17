<?php

namespace Vengine\Libs\ServiceCollectors;

class ArrayServiceCollector extends ConfigFileServiceCollector
{
    public function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }
}
