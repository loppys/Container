<?php

namespace Loader\System\Interfaces;

use Loader\System\Config;
use Loader\System\Storage\CommonObjectStorage;
use ReflectionMethod;

interface ContainerInterface
{
    public function build(PackageInterface $package): ContainerInjection;

    public function getComponent(string $name, string $method, array $methodArguments): ContainerInjection;

    public function getConstructor(string $class): ?ReflectionMethod;

    public function addPackage(PackageInterface $package): ContainerInterface;

    public function getPackage(string $name): PackageInterface;

    public function newPackage(string $name): PackageInterface;

    public function getArguments(string $class, string $method): array;

    public function setStorage(StorageInterface $storage): ContainerInterface;

    public function setBuilder(BuilderInterface $builder): ContainerInterface;

    public function getStorage(): StorageInterface;

    public function getBuilder(): BuilderInterface;

    public function getConfig(): Config;

    public function getObjectStorage(): CommonObjectStorage;

    public function packageCollect(array $packageList): bool;
}
