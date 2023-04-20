<?php

namespace Loader\Libraries\Alias\Interfaces;

use Loader\Libraries\Alias\DTO\Alias;

interface AliasInterface
{
    public static function getNew(string $name): Alias;

    public function getName(): string;

    public function getClassName(): string;
    
    public function setClassName(string $class): AliasInterface;

    public function add(string $alias): AliasInterface;

    public function remove(string $alias): AliasInterface;

    public function setList(array $aliasList): AliasInterface;

    public function has(string $alias): bool;
    
    public function get(string $name): string;
}
