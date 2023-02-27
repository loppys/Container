<?php

namespace Loader\System\Interfaces;

use ReflectionMethod;

interface BuilderAdapterInterface
{
    /**
     * @param string $class
     * @param array $arguments
     *
     * @return mixed
     */
    public function createObject(string $class, array $arguments = []);

    public function getComponent(string $name, string $method, array $methodArguments): ContainerInjection;

    public function getConstructor(string $class): ?ReflectionMethod;

    public function build(PackageInterface $package): ContainerInjection;

    public function getNew(string $name): ContainerInjection;
}
