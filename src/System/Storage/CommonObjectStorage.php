<?php

namespace Loader\System\Storage;

use Loader\System\Interfaces\StorageInterface;

class CommonObjectStorage extends DataStorage
{
    protected static $objectList = [];

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

    public static function getObjectList(): array
    {
        return self::$objectList;
    }
}
