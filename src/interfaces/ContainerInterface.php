<?php

namespace Vengine\Libs\interfaces;

interface ContainerInterface extends \Psr\Container\ContainerInterface
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
}
