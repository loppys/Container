<?php

namespace Loader\System\Storage;

use Loader\System\Interfaces\PackageInterface;
use Loader\System\Interfaces\StorageInterface;

class DataStorage implements StorageInterface
{
    protected static $data = [];

    public function add(string $name, $value): StorageInterface
    {
        if (!array_key_exists($name, $this->data)) {
            static::$data[$name] = $value;
        }

        return $this;
    }

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

    public function set(string $name, $value): StorageInterface
    {
        static::$data[$name] = $value;

        return $this;
    }

    public function setData(array $data): StorageInterface
    {
        static::$data = $data;

        return $this;
    }

    public function has(string $name): bool
    {
        return !empty($this->get($name));
    }

    public function get(string $name): ?PackageInterface
    {
        return static::$data[$name] ?? null;
    }

    public function getDataList(): array
    {
        return static::$data;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getProperty(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return '';
    }
}
