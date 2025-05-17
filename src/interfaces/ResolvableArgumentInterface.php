<?php

namespace Vengine\Libs\interfaces;

interface ResolvableArgumentInterface extends ArgumentInterface
{
    public function getValue(): string;
}
