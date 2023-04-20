<?php

namespace Loader\Libraries\Alias\Interfaces;

use Loader\Libraries\Alias\DTO\Alias;
use Loader\Libraries\Alias\Storages\AliasStorage;

interface AliasAdapterInterface
{
    public function addAlias(string $name, string $alias): AliasAdapterInterface;
    
    public function removeAlias(string $name): AliasAdapterInterface;

    public function setAlias(AliasInterface $alias): AliasAdapterInterface;
    
    public function getAlias(string $name): Alias;

    public function setClassNameForAlias(string $name, string $class): AliasInterface;

    public function hasAliasByName(string $name, string $alias): bool;

    public function hasAliasInStorage(string $name): bool;
    
    public function getAliasStorage(): AliasStorage;
}
