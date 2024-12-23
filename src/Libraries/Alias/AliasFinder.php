<?php

namespace Loader\Libraries\Alias;

use Loader\System\Container;
use Loader\Libraries\Alias\Exceptions\AliasException;

class AliasFinder
{
    public static function findReference(string $name): string
    {
        $container = Container::getInstance();

        if (class_exists($name)) {
            return $name;
        }

        if (!$container->hasAliasInStorage($name)) {
            return $name;
        }

        $alias = $container->getAlias($name);
        $class = $alias->getClassName();

        $classInterfaces = class_implements($class);

        if (interface_exists($name) && in_array($alias->getName(), $classInterfaces, true)) {
            return $class;
        }

        if ($alias->has($name)) {
            $aliasName = $alias->get($name);

            if (interface_exists($aliasName) && in_array($aliasName, $classInterfaces, true)) {
                return $class;
            }
        }

        throw new AliasException("Reference not found! Check {$name}");
    }
}
