<?php

namespace Loader;

use Loader\Builder\Storage;
use Loader\Builder\Builder;
use \ReflectionClass;
use \ReflectionMethod;

class Process
{
  public const GROUP_MODULES = Storage::GROUP_MODULES;
  public const GROUP_COMMON = Storage::GROUP_COMMON;
  public const GROUP_SYSTEM = Storage::GROUP_SYSTEM;
  public const GROUP_COMPONENT = Storage::GROUP_COMPONENT;

  public static function callModule(
    string $name,
    array $param = [],
    string $group = '',
    bool $merge = false
  ): ?object {
    if (!Storage::$configIsset) {
      Storage::setDefault();
    }

    $info = Storage::get($name);

    if (empty($info['handler']) || !class_exists($info['handler'])) {
      return null;
    }

    if (mb_stripos($info['handler'], 'Vengine') !== false) {
      $local = str_replace('Vengine', 'Local', $info['handler']);
    } else {
      $local = 'Local\\' . $info['handler'];
    }

    if (class_exists($local)) {
      Storage::change($info['name'], [
        'handler' => $local
      ]);

      $info['handler'] = $local;
    }

    $rc = self::getConstructor($info['handler']);

    if (!empty($rc)) {
      $params = self::getParameters($rc->class);
    }

    if (count($params) > 0) {
      foreach ($params as $key => $value) {
        if (empty($value->getType())) {
          continue;
        }

        $class = $value->getType()->getName();

        if (!class_exists($class)) {
          continue;
        }

        $moduleName = (new ReflectionClass($class))->getShortName();

        if (Storage::has($moduleName)) {
          $info['param'][] = self::callModule($moduleName);
        } else {
          $info['param'][] = self::getComponent($class);
        }
      }

      Storage::change($info['name'], ['param' => $info['param']]);
    }

    if (!empty($info['call'])) {
      if (is_array($info['call'])) {
        foreach ($info['call'] as $name) {
          $info['param'][] = self::callModule($info['call']);
        }
      } else {
        $info['param'][] = self::callModule($info['call']);
      }

      Storage::change($info['name'], ['param' => $info['param']]);
    }

    if (empty($info['group'])) {
      $info['group'] = Storage::GROUP_COMMON;
    }

    if (!empty($param)) {
      if ($merge) {
        $info['param'] = array_merge($info['param'], $param);
      } else {
        $info['param'] = $param;
      }
    }

    if ($group) {
      $info['group'] = $group;
    }

    return Builder::create($info, $info['group']);
  }

  public static function getConstructor(string $class = ''): ?ReflectionMethod
  {
    if (empty($class)) {
      return null;
    }

    return (new ReflectionClass($class))->getConstructor();
  }

  public static function getParameters(string $class = ''): array
  {
    if (empty($class)) {
      return [];
    }

    return (new ReflectionMethod($class, '__construct'))->getParameters();
  }

  public static function addModule(
    string $name,
    string $group,
    string $handler = '',
    array $param = [],
    string $path = ''
  ): void {
    $data = [
      'name' => $name,
      'group' => $group,
      'handler' => $handler,
      'param' => $param,
      'path' => $path
    ];

    Storage::add($name, Storage::GROUP_MODULES, $data);
  }

  public static function getComponent(string $name): ?object
  {
    if (mb_stripos($name, 'Vengine') !== false) {
      $local = str_replace('Vengine', 'Local', $name);
    } else {
      $local = 'Local\\' . $name;
    }

    if (class_exists($local)) {
      return self::getComponent($local);
    }

    if (class_exists($name)) {
      $tmpName = (new \ReflectionClass($name))->getShortName();
    }

    if (!Storage::has($tmpName ?: $name)) {
      Storage::add(
        $tmpName ?: $name,
        Storage::GROUP_COMPONENT,
        [
          'name' => $tmpName ?: $name,
          'handler' => $name
        ]
      );
    }

    $params = [];

    $info = Storage::get($tmpName ?: $name);

    if ($object = $info['object']) {
      return $object;
    }

    if (empty($info['handler']) || !class_exists($info['handler'])) {
      return null;
    }

    $rc = self::getConstructor($info['handler']);

    if (!empty($rc)) {
      $params = self::getParameters($rc->class);
    }

    if (count($params) > 0) {
      foreach ($params as $key => $value) {
        if (empty($value->getType())) {
          continue;
        }

        $class = $value->getType()->getName();

        if (!class_exists($class)) {
          continue;
        }

        $info['param'][] = self::getComponent($class);
      }

      Storage::change($info['name'], ['param' => $info['param']]);
    }

    return Builder::create($info);
  }

  public static function __callStatic($name, $arguments)
  {
    $class = new ReflectionClass(Storage::class);
    $method = $class->getMethod($name);

    if ($method->getReturnType()->getName() === 'void') {
      $method->invoke(null, ...$arguments);

      return;
    }

    return $method->invoke(null, ...$arguments);
  }
}
