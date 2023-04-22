<?php

namespace Loader\Libraries\Alias;

use Loader\Libraries\Alias\DTO\Alias;
use Loader\Libraries\Alias\Interfaces\AliasAdapterInterface;
use Loader\Libraries\Alias\Interfaces\AliasInterface;
use Loader\Libraries\Alias\Storages\AliasStorage;

class AliasAdapter implements AliasAdapterInterface
{
    /**
     * @var AliasStorage
     */
    private $storage;

    public function __construct(AliasStorage $storage)
    {
        $this->storage = $storage;
    }

    public function addAlias(string $name, string $alias): AliasAdapterInterface
    {
        $this->getAlias($name)->add($alias);

        return $this;
    }

    public function removeAlias(string $name): AliasAdapterInterface
    {
        $this->getAlias($name);
        
        return $this;
    }

    public function setAlias(AliasInterface $alias): AliasAdapterInterface
    {
        $this->storage->set($alias->getName(), $alias);
        
        return $this;
    }

    public function getAlias(string $name): Alias
    {
        return $this->storage->get($name);
    }

    public function setClassNameForAlias(string $name, string $class): AliasInterface
    {
        return $this->getAlias($name)->setClassName($class);
    }

    public function hasAliasByName(string $name, string $alias): bool
    {
        if (!$this->hasAliasInStorage($name)) {
            return false;
        }
        
        return $this->getAlias($name)->has($alias);
    }

    public function hasAliasInStorage(string $name): bool
    {
        return $this->storage->has($name);
    }
    
    public function getAliasStorage(): AliasStorage
    {
        return $this->storage;
    }
}
