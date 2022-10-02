<?php

namespace Loader\Builder;

use Loader\Builder\Storage;
use \ReflectionClass;
use \ReflectionMethod;
use Loader\Process;

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
          return self::createCommonObject($info['handler'], $info['param'], $info['name'], $info['method']);
        }
        break;
      case self::TYPE_MODULES:
        if ($object = Storage::get($info['name'] ?: '')['object']) {
          return $object;
        } else {
          return self::createCommonObject($info['handler'], $info['param'], $info['name'], $info['method']);
        }
        break;
      case self::TYPE_SYSTEM:
        if ($info['object'] === true) {
          return null;
        }

        $object = self::createCommonObject($info['handler'], $info['param'], $info['name'], $info['method']);
        return $object;
        break;

      default:
        return self::createCommonObject($info['handler'], $info['param'], $info['name'], $info['method']);
        break;
    }
  }

  public static function createCommonObject(string $class, ?array $param = null, string $name = '', ?string $method = ''): ?object
  {
    $object = null;

    if (class_exists($class)) {
      if ($param) {
        $object = new $class(...$param);
      } else {
        $object = new $class();
      }

      if ($defaultMethod = $object->defaultMethod) {
        $method = $defaultMethod;
      }

      if ($classSettings = $object->classSettings) {
        $object->classSettings = Process::getComponent($classSettings);
      }

      if ($object && ($method && method_exists($object, $method))) {
        call_user_func_array([$object, $method], []);
      }

      if (!empty($name)) {
        Storage::change($name, ['object' => $object]);
      }
    }

    return $object;
  }
}
