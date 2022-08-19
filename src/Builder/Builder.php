<?php

namespace Loader\Builder;

use Loader\Builder\Storage;
use Loader\Process as Loader;

class Builder
{
  public const TYPE_COMMON = 'common';
  public const TYPE_MODULES = 'modules';
  public const TYPE_SYSTEM = 'system';

  public static function create(array $info, string $type = ''): ?object
  {
    switch ($type) {
      case self::TYPE_COMMON:
        $object = self::getObject($info['handler'], $info['param'], $info['name']);
        Storage::change($info['name'], ['object' => $object]);
        return $object;
        break;
      case self::TYPE_MODULES:
        $object = self::getObject($info['handler'], $info['param'], $info['name']);
        Storage::change($info['name'], ['object' => $object]);
        return $object;
        break;
      case self::TYPE_SYSTEM:
        if ($info['object'] === true) {
          return null;
        }

        $object = self::getObject($info['handler'], $info['param'], $info['name']);
        Storage::change($info['name'], ['object' => $object]);
        return $object;
        break;

      default:
        return null;
        break;
    }
  }

  private static function getObject(string $class, ?array $param, string $name = ''): ?object
  {
    if ($object = Storage::get($name)['object']) {
      return $object;
    }

    $object = null;

    if (class_exists($class)) {
      if ($param) {
        $object = new $class(...$param);
      } else {
        $object = new $class();
      }
    }

    return $object;
  }
}
