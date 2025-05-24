<?php

namespace Vengine\Libs\DI\Alias;

use ArrayAccess;
use JsonSerializable;
use InvalidArgumentException;
use LogicException;

class Alias implements ArrayAccess, JsonSerializable
{
    public function __construct(
        protected string $name,
        protected string $aliasName,
        protected string $groupKey,
        protected int $priority
    ) {
    }

    public function getAliasName(): string
    {
        return $this->aliasName;
    }

    public function setAliasName(string $aliasName): static
    {
        $this->aliasName = $aliasName;

        return $this;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGroupKey(): string
    {
        return $this->groupKey;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'aliasName' => $this->aliasName,
            'groupKey' => $this->groupKey,
            'priority' => $this->priority,
        ];
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['name'], $data['aliasName'], $data['groupKey'], $data['priority'])) {
            throw new InvalidArgumentException("Invalid data for Alias creation");
        }

        return new static(
            $data['name'],
            $data['aliasName'],
            $data['groupKey'],
            (int)$data['priority']
        );
    }

    public function offsetExists(mixed $offset): bool
    {
        return in_array($offset, ['name', 'aliasName', 'groupKey', 'priority'], true);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->$offset ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!property_exists($this, $offset)) {
            throw new InvalidArgumentException("Property {$offset} does not exist.");
        }

        $this->$offset = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException("Cannot unset properties of Alias");
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
