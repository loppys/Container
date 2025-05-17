<?php

namespace Vengine\Libs\Definitions;

use Generator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\interfaces\DefinitionAggregateInterface;
use Vengine\Libs\interfaces\DefinitionInterface;
use Vengine\Libs\traits\ContainerAwareTrait;

class DefinitionAggregate implements DefinitionAggregateInterface
{
    use ContainerAwareTrait;

    public function __construct(
        /** @var DefinitionInterface[] */
        protected array $definitions = []
    ) {
        $this->definitions = array_filter($this->definitions, static function ($definition) {
            return ($definition instanceof DefinitionInterface);
        });
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function replace(string $id, $newDefinition): void
    {
        if ($this->has($id)) {
            $def = $this->getDefinition($id);

            $def->replaceKeys($newDefinition);
        }
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function add(string $id, $definition, bool $overwrite = false): DefinitionInterface
    {
        if (true === $overwrite) {
            $this->remove($id);
        }

        if (false === ($definition instanceof DefinitionInterface)) {
            $definition = new Definition($id, $definition);
        }

        $definition->setContainer($this->getContainer());
        $definition->fetchConstructor();

        $this->definitions[] = $definition->setId($id);

        return $definition;
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
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
            if ($id === $definition->getId()) {
                return true;
            }
        }

        return false;
    }

    public function hasSharedTag(string $tag): bool
    {
        foreach ($this as $definition) {
            if ($definition->hasSharedTag($tag)) {
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
            if ($id === $definition->getId()) {
                return $definition->setContainer($this->getContainer());
            }
        }

        throw new NotFoundException(sprintf('Alias (%s) is not being handled as a definition.', $id));
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function resolve(string $id, array $arguments = []): mixed
    {
        return $this->getDefinition($id)->resolve($arguments);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function resolveNew(string $id, array $arguments = []): mixed
    {
        return $this->getDefinition($id)->resolveNew($arguments);
    }

    /**
     * @throws ContainerException
     */
    public function resolveTagged(string $tag, array $arguments = []): array
    {
        $arrayOf = [];

        foreach ($this as $definition) {
            if ($definition->hasTag($tag)) {
                $arrayOf[] = $definition->setContainer($this->getContainer())->resolve($arguments);
            }
        }

        return $arrayOf;
    }

    /**
     * @throws ContainerException
     */
    public function resolveTaggedNew(string $tag, array $arguments = []): array
    {
        $arrayOf = [];

        foreach ($this as $definition) {
            if ($definition->hasSharedTag($tag)) {
                $arrayOf[] = $definition->setContainer($this->getContainer())->resolveNew($arguments);
            }
        }

        return $arrayOf;
    }

    public function remove(string $id): void
    {
        $id = Definition::normaliseAlias($id);

        foreach ($this as $key => $definition) {
            if ($id === $definition->getId()) {
                unset($this->definitions[$key]);
            }
        }
    }

    public function getIterator(): Generator
    {
        yield from $this->definitions;
    }
}
