<?php

namespace Vengine\Libs\Aliases;

use RuntimeException;

class AliasFinder
{
    private AliasStorage $storage;

    public function __construct(?AliasStorage $storage = null)
    {
        $this->storage = $storage ?? new AliasStorage();
    }

    public function registerAlias(string $reference, string $alias): void
    {
        $this->storage->registerAlias($reference, $alias);
    }

    /**
     * @throws RuntimeException
     */
    public function findAlias(string $reference): string
    {
        $aliases = $this->storage->getAliases($reference);

        if (empty($aliases)) {
            throw new RuntimeException("Алиасы для '{$reference}' не найдены.");
        }

        return $aliases[0];
    }

    public function findAllAliases(string $reference): array
    {
        return $this->storage->getAliases($reference);
    }
}
