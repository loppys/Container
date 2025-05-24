<?php

namespace Vengine\Libs\DI\interfaces;

interface ResolvableArgumentInterface extends ArgumentInterface
{
    public function getValue(): string;
}
