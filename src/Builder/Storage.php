<?php

namespace Loader\Builder;

use Vengine\Modules\Cache\Cache;

class Storage
{
  public const GROUP_MODULES = 'modules';
  public const GROUP_COMMON = 'common';
  public const GROUP_SYSTEM = 'system';
  public const GROUP_COMPONENT = 'component';

  private static $data = [];
  private static $infoData = [];

  private static $cacheObject;

  public static $configIsset = false;

  public static function setDefault(): void
  {
    $config = require_once 'config.php';

    if (is_array($config)) {
      foreach ($config as $key => $value) {
        $data = [
          'name' => $value['name'],
          'group' => $value['group'] ?: self::GROUP_COMMON,
          'handler' => $value['handler'],
          'param' => $value['param'],
          'path' => $value['path'],
          'package' => $value['package'],
          'create' => $value['create'],
          'call' => $value['call'],
          'object' => null
        ];

        self::add($value['name'], $value['group'] ?: self::GROUP_COMMON, $data);
      }

      self::$configIsset = true;
    }
  }

  public static function change(string $name, array $property): void
  {
    $group = self::getGroupByName($name);

    if (is_array($property)) {
      foreach ($property as $key => $value) {
        self::$data[$group][$name][$key] = $value;
      }

      self::updateCache();
    }
  }

  public static function add(string $name, string $group, $data): void
  {
    if (empty(self::$cacheObject)) {
      self::$cacheObject = new Cache('Loader.cache.data');
    }

    self::$infoData[$name] = $group;
    self::$data[$group][$name] = $data;

    if ($cache = self::getCacheData()) {
      if (empty($cache[$group][$name])) {
        self::updateCache();
      }
    }
  }

  public static function getGroupByName($name): string
  {
    return self::$infoData[$name] ?: '';
  }

  public static function delete(string $name): void
  {
    $item = self::$data[self::getGroupByName($name)][$name];

    if (!empty($item) && $item['type'] !== self::GROUP_SYSTEM) {
      unset(self::$data[$name]);
    }
  }

  public static function has(string $name): bool
  {
    return !empty(self::get($name));
  }

  public static function get(string $name): array
  {
    $group = self::getGroupByName($name);

    if (empty(self::$cacheObject)) {
      self::$cacheObject = new Cache('Loader:data');
    }

    if (self::$cacheObject->initCacheData()) {
      $cacheData = self::getCacheData();
    } else {
      self::updateCache();

      $cacheData = self::getCacheData();
    }

    if ($data = $cacheData[$group][$name]) {
      return $data;
    }

    return self::$data[$group][$name] ?: [];
  }

  public static function set($data): void
  {
    self::$data = $data;
  }

  public static function updateCache(): void
  {
    if (empty(self::$cacheObject)) {
      self::$cacheObject = new Cache('Loader:data');
    }

    $data = self::$data;

    foreach ($data as $key => $value) {
      unset($data[$key][self::GROUP_SYSTEM]);
    }

    self::$cacheObject->updateCacheData($data);
  }

  public static function getCacheData()
  {
    if (empty(self::$cacheObject)) {
      self::$cacheObject = new Cache('Loader:data');
    }

    $cache = self::$cacheObject;

    if ($cache->initCacheData()) {
      $data = $cache->getCacheData();

      if (array_diff((array)$data, self::$data)) {
        $data = self::$data;

        foreach ($data as $key => $value) {
          unset($data[$key][self::GROUP_SYSTEM]);
        }

        $cache->updateCacheData($data);
      }

      return $cache->getCacheData();
    } else {
      $data = self::$data;

      foreach ($data as $key => $value) {
        unset($data[$key][self::GROUP_SYSTEM]);
      }

      if ($cache->updateCacheData($data)) {
        return $cache->getCacheData();
      }
    }

    return [];
  }

  public static function getList(string $group): array
  {
    return self::$data[$group] ?: [];
  }

  public static function getData(): array
  {
    return self::$data;
  }

  public static function getPackage($name): ?object
  {
    $result = null;

    if ($package = self::get($name)['package']) {
      if (is_string($package) && class_exists($package)) {
        $result = \Loader\Process::getComponent($package);

        self::change(
          $name,
          [
            'package' => $result
          ]
        );
      }
    }

    return $result;
  }
}
