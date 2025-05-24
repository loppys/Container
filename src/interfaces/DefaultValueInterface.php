<?php

namespace Vengine\Libs\DI\interfaces;

interface DefaultValueInterface extends ArgumentInterface
{
    public function setDefaultValue(mixed $value): static;
    public function getDefaultValue(): mixed;
}
