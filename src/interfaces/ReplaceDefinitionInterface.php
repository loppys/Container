<?php

namespace Vengine\Libs\DI\interfaces;

interface ReplaceDefinitionInterface
{
    public function getReplace(): string;
    public function getService(): string;
}
