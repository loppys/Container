<?php

namespace Vengine\Libs\Definitions;

use Generator;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\interfaces\DefinitionAggregateInterface;
use Vengine\Libs\interfaces\DefinitionInterface;
use Vengine\Libs\traits\ContainerAwareTrait;

class DefinitionAggregate implements DefinitionAggregateInterface
{
    use ContainerAwareTrait;

    public function __construct(protected array $definitions = [])
    {
        $this->definitions = array_filter($this->definitions, static function ($definition) {
            return ($definition instanceof DefinitionInterface);
        });
    }

    public function add(string $id, $definition, bool $overwrite = false): DefinitionInterface
    {
        if (true === $overwrite) {
            $this->remove($id);
        }

        if (false === ($definition instanceof DefinitionInterface)) {
            $definition = new Definition($id, $definition);
        }

        $this->definitions[] = $definition->setAlias($id);

        return $definition;
    }

    public function addShared(string $id, $definition, bool $overwrite = false): DefinitionInterface
    {
        $definition = $this->add($id, $definition, $overwrite);
        return $definition->setShared(true);
    }

    public function has(string $id): bool
    {
        $id = Definition::normaliseAlias($id);

        /** @var DefinitionInterface $definition */
        foreach ($this as $definition) {
            if ($id === $definition->getAlias()) {
                return true;
            }
        }

        return false;
    }

    public function hasTag(string $tag): bool
    {
        foreach ($this as $definition) {
            if ($definition->hasTag($tag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function getDefinition(string $id): DefinitionInterface
    {
        $id = Definition::normaliseAlias($id);

        foreach ($this as $definition) {
            if ($id === $definition->getAlias()) {
                return $definition->setContainer($this->getContainer());
            }
        }

        throw new NotFoundException(sprintf('Alias (%s) is not being handled as a definition.', $id));
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function resolve(string $id): mixed
    {
        return $this->getDefinition($id)->resolve();
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function resolveNew(string $id): mixed
    {
        return $this->getDefinition($id)->resolveNew();
    }

    /**
     * @throws ContainerException
     */
    public function resolveTagged(string $tag): array
    {
        $arrayOf = [];

        foreach ($this as $definition) {
            if ($definition->hasTag($tag)) {
                $arrayOf[] = $definition->setContainer($this->getContainer())->resolve();
            }
        }

        return $arrayOf;
    }

    /**
     * @throws ContainerException
     */
    public function resolveTaggedNew(string $tag): array
    {
        $arrayOf = [];

        foreach ($this as $definition) {
            if ($definition->hasTag($tag)) {
                $arrayOf[] = $definition->setContainer($this->getContainer())->resolveNew();
            }
        }

        return $arrayOf;
    }

    public function remove(string $id): void
    {
        $id = Definition::normaliseAlias($id);

        foreach ($this as $key => $definition) {
            if ($id === $definition->getAlias()) {
                unset($this->definitions[$key]);
            }
        }
    }

    public function getIterator(): Generator
    {
        yield from $this->definitions;
    }
}
