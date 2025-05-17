<?php

namespace Vengine\Libs\interfaces;

use Vengine\Libs\Inflector;

interface InflectorAggregateInterface extends AggregateInterface
{
    public function add(string $type, ?callable $callback = null): Inflector;
    public function inflect(object $object);
}
