<?php

namespace Loader\System\Interfaces;

use Loader\System\Config;
use Loader\System\Storage\CommonObjectStorage;
use ReflectionMethod;

interface ContainerInterface
{
    public function build(PackageInterface $package): ContainerInjection;

    public function getComponent(string $name, string $method, array $methodArguments): ContainerInjection;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getShared(string $name);

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return ContainerInterface
     */
    public function setShared(string $name, $value): ContainerInterface;

    public function getNew(string $name): ContainerInjection;

    /**
     * @param string $class
     * @param array $arguments
     *
     * @return mixed
     */
    public function createObject(string $class, array $arguments = []);

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
