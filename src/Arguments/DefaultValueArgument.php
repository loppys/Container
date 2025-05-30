<?php

namespace Vengine\Libs\DI\Arguments;

use Vengine\Libs\DI\interfaces\DefaultValueInterface;

class DefaultValueArgument extends ResolvableArgument implements DefaultValueInterface
{
    public function __construct(string $value, protected mixed $defaultValue = null)
    {
        parent::__construct($value);
    }

    public function setDefaultValue(mixed $value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
}
