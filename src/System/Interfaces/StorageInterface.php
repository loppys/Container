<?php

namespace Loader\System\Interfaces;

interface StorageInterface
{
    public function add(string $name, $value): StorageInterface;

    public function delete(string $name): StorageInterface;

    public function set(string $name, $value): StorageInterface;

    public function replace(string $name, $value): StorageInterface;

    public function has(string $name): bool;

    public function get(string $name): ?PackageInterface;

    public function getDataList(): array;
}
