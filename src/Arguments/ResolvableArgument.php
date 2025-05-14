<?php

namespace Vengine\Libs\Arguments;

use Vengine\Libs\interfaces\ResolvableArgumentInterface;

class ResolvableArgument implements ResolvableArgumentInterface
{
    public function __construct(protected string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
