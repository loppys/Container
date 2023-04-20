<?php

namespace Loader\System\Interfaces;

interface PackageInterface
{
    public function __construct(string $name);

    public function update(array $data): PackageInterface;

    public function setName(string $name): PackageInterface;

    public function setObject(ContainerInjection $object): PackageInterface;

    public function setClassName(string $class): PackageInterface;

    public function setArguments(array $arguments): PackageInterface;

    public function setDefaultMethod(string $method): PackageInterface;

    public function setDefaultMethodArguments(array $arguments): PackageInterface;

    public function getName(): string;

    public function getObject(): ContainerInjection;

    public function getClassName(): string;

    public function getArguments(): array;

    public function getDefaultMethod(): string;

    public function getDefaultMethodArguments(): array;

    public function hasObject(): bool;
}
