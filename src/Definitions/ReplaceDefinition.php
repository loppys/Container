<?php

namespace Vengine\Libs\Definitions;

use Vengine\Libs\interfaces\ReplaceDefinitionInterface;

class ReplaceDefinition implements ReplaceDefinitionInterface
{
    public function __construct(
        protected readonly string $service,
        protected readonly string $replace,
    ) {}

    public function getReplace(): string
    {
        return $this->replace;
    }

    public function getService(): string
    {
        return $this->service;
    }
}
