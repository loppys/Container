<?php

namespace Loader;

use Loader\Builder\Storage;
use Loader\Builder\Builder;

class Process extends Storage
{
  public static function callModule(
    string $name,
    array $param = [],
    string $group = '',
    bool $merge = false
  ): ?object {
    if (!parent::$configIsset) {
      parent::setDefault();
    }

    $info = parent::get($name);

    if (empty($info['handler'])) {
      return null;
    }

    $rm = new \ReflectionMethod($info['handler'], '__construct');
    $params = $rm->getParameters();

    if (count($params) > 0) {
      $type = [];

      foreach ($params as $key => $value) {
        if (empty($value->getType())) {
          continue;
        }

        if (!class_exists($value->getType()->getName())) {
          continue;
        }

        $name = (new \ReflectionClass($value->getType()->getName()))->getShortName();

        $info['param'][] = self::callModule($name);

        parent::change($info['name'], ['param' => $info['param']]);
      }
    }

    if (!empty($info['call'])) {
      if (is_array($info['call'])) {
        foreach ($info['call'] as $name) {
          $info['param'][] = self::callModule($info['call']);
        }
      } else {
        $info['param'][] = self::callModule($info['call']);
      }

      parent::change($info['name'], ['param' => $info['param']]);
    }

    if (empty($info['group'])) {
      $info['group'] = parent::GROUP_COMMON;
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

    if (self::issetGroup($info['group'])) {
      return Builder::create($info, $info['group']);
    }

    return null;
  }

  public static function issetGroup(string $group): bool
  {
    $allGroup = [
      parent::GROUP_COMMON,
      parent::GROUP_SYSTEM,
      parent::GROUP_MODULES,
    ];

    return in_array($group, $allGroup);
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

    parent::add($name, parent::GROUP_MODULES, $data);
  }
}
