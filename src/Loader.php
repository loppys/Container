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

    switch ($info['group']) {
      case parent::getGroupByName($name) === parent::GROUP_MODULES:
        if ($info['package']) {
          return Builder::create($info, parent::GROUP_MODULES);
        }
        break;
      case parent::getGroupByName($name) === parent::GROUP_COMMON:
        if ($info['handler']) {
          return Builder::create($info, parent::GROUP_COMMON);
        }
        break;
      case parent::getGroupByName($name) === parent::GROUP_SYSTEM:
        return Builder::create($info, parent::GROUP_SYSTEM);
        break;

      default:
        return null;
        break;
    }

    return null;
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
