<?php

namespace Loader\System;

class Config
{
  protected $data = [];

  public function configCollect(array $configArr): void
  {
    foreach ($configArr as $key => $value) {
      if (empty($value)) {
        continue;
      }

      if (is_string($value)) {
        $this->addConfigData($value);
      }

      if (is_array($value)) {
        foreach ($value as $class => $method) {
          $this->addConfigData($class, $method);
        }
      }

      if (is_string($key)) {
        $this->replaceClass($value, $key);
      }
    }
  }

  public function getConfigData(): array
  {
    return $this->data;
  }

  private function addConfigData(string $class, string $method = ''): void
  {
    $name = explode('\\', $class);
    $name = array_pop($name);

    $this->data[$name] = [
      'class' => $class,
      'method' => $method
    ];
  }

  private function replaceClass(string $oldClass, string $newClass, string $method = ''): void
  {
    $name = explode('\\', $oldClass);
    $name = array_pop($name);

    $this->data[$oldName] = [
      'class' => $newClass,
      'method' => $method
    ];
  }
}
