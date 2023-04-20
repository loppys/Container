<?php

namespace Loader\System\Interfaces;

use Loader\System\Config;
use Loader\System\Storage\CommonObjectStorage;

interface ContainerInterface
{
    public function getArguments(string $class, string $method): array;

    public function setStorage(StorageInterface $storage): ContainerInterface;

    public function setBuilder(BuilderInterface $builder): ContainerInterface;

    public function getStorage(): StorageInterface;

    public function getBuilder(): BuilderInterface;

    public function getConfig(): Config;

    public function getObjectStorage(): CommonObjectStorage;

    public function packageCollect(array $packageList): bool;
}
