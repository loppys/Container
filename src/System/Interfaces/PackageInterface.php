<?php

namespace Loader\System\Interfaces;

interface PackageInterface
{
    public function __construct(string $name);

    public function update(array $data): PackageInterface;

    public function setName(string $name): void;

    public function setObject(ContainerInjection $object): void;

    public function setClassName(string $class): void;

    public function setArguments(array $arguments): void;

    public function setDefaultMethod(string $method): void;

    public function setDefaultMethodArguments(array $arguments): void;

    public function getName(): string;

    public function getObject(): ContainerInjection;

    public function getClassName(): string;

    public function getArguments(): array;

    public function getDefaultMethod(): string;

    public function getDefaultMethodArguments(): array;

    public function hasObject(): bool;
}
