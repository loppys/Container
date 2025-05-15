<?php

namespace Vengine\Libs\interfaces;

interface DefinitionInterface extends ContainerAwareInterface
{
    public function getId(): string;
    public function setId(string $id): DefinitionInterface;
    public function addArgument(ArgumentInterface $arg): DefinitionInterface;
    public function addRawArgument(mixed $value, ?string $name = null): DefinitionInterface;
    public function addArguments(array $args): DefinitionInterface;
    public function addMethodCall(string $method, array $args = []): DefinitionInterface;
    public function addMethodCalls(array $methods = []): DefinitionInterface;
    public function replaceProperties(array $properties = []): DefinitionInterface;
    public function addRefs(array ...$refs): DefinitionInterface;
    public function addSharedTags(array $tags): DefinitionInterface;
    public function getConcrete(): mixed;
    public function hasSharedTag(string $tag): bool;
    public function isShared(): bool;
    public function resolve(): mixed;
    public function resolveNew(): mixed;
    public function setConcrete(mixed $concrete): DefinitionInterface;
    public function setShared(bool $shared): DefinitionInterface;
    public function fetchConstructor(): void;
}
