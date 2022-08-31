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
        if ($object = Storage::get($info['name'] ?: '')['object']) {
          return $object;
        } else {
          return self::createCommonObject($info['handler'], $info['param'], $info['name']);
        }
        break;
      case self::TYPE_MODULES:
        if ($object = Storage::get($info['name'] ?: '')['object']) {
          return $object;
        } else {
          return self::createCommonObject($info['handler'], $info['param'], $info['name']);
        }
        break;
      case self::TYPE_SYSTEM:
        if ($info['object'] === true) {
          return null;
        }

        $object = self::createCommonObject($info['handler'], $info['param'], $info['name']);
        return $object;
        break;

      default:
        return null;
        break;
    }
  }

  public static function createCommonObject(string $class, ?array $param = null, string $name = ''): ?object
  {
    $object = null;

    if (class_exists($class)) {
      if ($param) {
        $object = new $class(...$param);
      } else {
        $object = new $class();
      }

      if (!empty($name)) {
        Storage::change($name, ['object' => $object]);
      }
    }

    return $object;
  }
}
