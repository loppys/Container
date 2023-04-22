<?php

namespace Loader\System\Interfaces;

interface StorageInterface
{
    /**
     * @param string $name
     * @param mixed $value
     *
     * @return StorageInterface
     */
    public function add(string $name, $value): StorageInterface;

    public function delete(string $name): StorageInterface;

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return StorageInterface
     */
    public function set(string $name, $value): StorageInterface;

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return StorageInterface
     */
    public function replace(string $name, $value): StorageInterface;

    public function has(string $name): bool;

    /**
     * @param string $name
     * 
     * @return mixed
     */
    public function get(string $name);

    public function getDataList(): array;
}
