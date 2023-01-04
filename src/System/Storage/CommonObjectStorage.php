<?php

namespace Loader\System\Storage;

use Loader\System\Interfaces\StorageInterface;

class CommonObjectStorage extends DataStorage
{
    protected static $objectList = [];

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return StorageInterface
     */
    public function add(string $name, $value): StorageInterface
    {
        if (!array_key_exists($name, static::$objectList)) {
            static::$objectList[$name] = $value;
        }

        return $this;
    }

    public function getObject(string $name): ?object
    {
        return static::$objectList[$name] ?? null;
    }

    public function getObjectList(): array
    {
        return self::$objectList;
    }

    public function delete(string $name): StorageInterface
    {
        if (array_key_exists($name, static::$objectList)) {
            unset(static::$objectList[$name]);
        }

        return $this;
    }

    public function has(string $name): bool
    {
        return !empty($this->getObject($name));
    }
}
