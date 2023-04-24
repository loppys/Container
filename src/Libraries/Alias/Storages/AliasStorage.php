<?php

namespace Loader\Libraries\Alias\Storages;

use Loader\Libraries\Alias\DTO\Alias;
use Loader\Libraries\Alias\Exceptions\AliasException;
use Loader\System\Interfaces\StorageInterface;

class AliasStorage implements StorageInterface
{
    private static $data = [];
    
    /**
     * @inheritDoc
     */
    public function add(string $name, $value): StorageInterface
    {
        if (!array_key_exists($name, static::$data)) {
            static::$data[$name] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function replace(string $name, $value): StorageInterface
    {
        if (array_key_exists($name, static::$data)) {
            $this->set($name, $value);
        }

        return $this;
    }

    public function delete(string $name): StorageInterface
    {
        if (array_key_exists($name, static::$data)) {
            unset(static::$data[$name]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function set(string $name, $value): StorageInterface
    {
        static::$data[$name] = $value;

        return $this;
    }

    public function has(string $name): bool
    {
        return !empty(static::$data[$name]) && static::$data[$name] instanceof Alias;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): Alias
    {
        if (!$this->has($name)) {
            throw new AliasException("alias {$name} not found");
        }
        
        return static::$data[$name];
    }

    public function getDataList(): array
    {
        return static::$data;
    }

    /**
     * @inheritDoc
     */
    public function getProperty(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return '';
    }
}
