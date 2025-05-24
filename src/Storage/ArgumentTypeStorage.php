<?php

namespace Vengine\Libs\DI\Storage;

use ReflectionClass;

class ArgumentTypeStorage
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_BOOL = 'boolean';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_CALLABLE = 'callable';
    public const TYPE_DOUBLE = 'double';
    public const TYPE_FLOAT = 'double';
    public const TYPE_INT = 'integer';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_OBJECT = 'object';
    public const TYPE_STRING = 'string';

    public static function getList(): array
    {
        $reflect = new ReflectionClass(static::class);

        return $reflect->getConstants();
    }
}
