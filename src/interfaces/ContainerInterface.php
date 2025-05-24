<?php

namespace Vengine\Libs\DI\interfaces;

interface ContainerInterface extends \Psr\Container\ContainerInterface, ContainerPackageInterface
{
    public function add(string $id, $concrete = null, bool $overwrite = false): DefinitionInterface;
    public function addRawService(string $id, array $service): DefinitionInterface;
    public function addServiceProvider(ServiceProviderInterface $provider): static;
    public function addShared(string $id, $concrete = null, bool $overwrite = false): DefinitionInterface;
    public function extend(string $id): DefinitionInterface;
    public function getNew(string $id): mixed;
    public function getWithArguments(string $id, array $args = []): mixed;
    public function getNewWithArguments(string $id, array $args = []): mixed;
    public function inflector(string $type, ?callable $callback = null): InflectorInterface;
    public function defaultToShared(bool $shared = true): ContainerInterface;
    public function defaultToOverwrite(bool $overwrite = true): ContainerInterface;
    public function getDefinition(string $id): DefinitionInterface;
}
