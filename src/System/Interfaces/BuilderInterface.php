<?php

namespace Loader\System\Interfaces;

interface BuilderInterface
{
    public function packageCollect(array $packageList): bool;

    public function build(PackageInterface $package, bool $new): ContainerInjection;

    public function getNew(string $name): ContainerInjection;

    /**
     * @param string $class
     * @param array $arguments
     *
     * @return object|null
     */
    public function createObject(string $class, array $arguments = []);

    /**
     * @param object $class
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function invoke(object $class, string $method, array $arguments = []);

    public function defineArguments(string $class, string $method): array;

    public function getContainer(): ContainerInterface;
}
