<?php

namespace Vengine\Libs\Aliases;

class AliasStorage
{
    private array $aliases = [];

    public function registerAlias(string $reference, string $alias): void
    {
        if (!isset($this->aliases[$reference])) {
            $this->aliases[$reference] = [];
        }

        if (!in_array($alias, $this->aliases[$reference], true)) {
            $this->aliases[$reference][] = $alias;
        }
    }

    public function getAliases(string $reference): array
    {
        return $this->aliases[$reference] ?? [];
    }

    public function hasAlias(string $reference, string $alias): bool
    {
        return isset($this->aliases[$reference]) && in_array($alias, $this->aliases[$reference], true);
    }
}
