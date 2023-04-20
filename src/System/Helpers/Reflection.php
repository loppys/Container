<?php

namespace Loader\System\Helpers;

use InvalidArgumentException;
use Loader\System\Exceptions\ClassNotFoundException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

class Reflection
{
    protected static $reflections = [];

    /**
     * @param object|string $class
     * @return ReflectionClass|null
     *
     * @throws ReflectionException
     */
    public static function get($class): ?ReflectionClass
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!is_string($class)) {
            throw new InvalidArgumentException('Class must be a string or object');
        }

        $lowerClass = strtolower($class);
        if (!static::has($class)) {
            static::set(new ReflectionClass($class), $lowerClass);
        }

        return static::$reflections[$lowerClass];
    }

    public static function has(string $class): bool
    {
        return static::rawHas(strtolower($class));
    }

    protected static function rawHas(string $class): bool
    {
        return !empty(static::$reflections[$class]);
    }

    public static function set(ReflectionClass $reflection, string $class = null): void
    {
        static::$reflections[strtolower($class ?: $reflection->getName())] = $reflection;
    }

    public static function attemptSet(ReflectionClass $reflection, string $class = null): void
    {
        $class = $class ?: $reflection->getName();

        if (!static::has($class)) {
            static::set($reflection, $class);
        }
    }

    public static function getObjectVars(object $object): array
    {
        return get_object_vars($object);
    }

    /**
     * @param string $class
     * @return ReflectionMethod|null
     *
     * @throws ReflectionException
     */
    public static function getConstructor(string $class): ?ReflectionMethod
    {
        if (!static::classExist($class)) {
            throw new ClassNotFoundException($class);
        }

        $result = null;

        if (method_exists($class, '__construct')) {
            $result = static::get($class)->getConstructor();
        }

        return $result;
    }

    /**
     * @param string $class
     * @param string $method
     * @return array
     *
     * @throws ReflectionException
     */
    public static function getArguments(string $class, string $method): array
    {
        if (!static::classExist($class)) {
            throw new ClassNotFoundException($class);
        }

        return (new ReflectionMethod($class, $method))->getParameters();
    }

    public static function getClassShortName($class): ?string
    {
        $name = null;

        if ($class && ($pos = strrpos($class, '\\')) !== false) {
            $name = substr($class, ++$pos);
        }

        return $name;
    }

    public static function classExist(string $className): bool
    {
        return class_exists($className) || interface_exists($className);
    }
}
