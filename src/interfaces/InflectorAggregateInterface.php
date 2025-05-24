<?php

namespace Vengine\Libs\DI\interfaces;

use Vengine\Libs\DI\Inflector;

interface InflectorAggregateInterface extends AggregateInterface
{
    public function add(string $type, ?callable $callback = null): Inflector;
    public function inflect(object $object);
}
