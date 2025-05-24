<?php

namespace Vengine\Libs\DI;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\interfaces\InflectorAggregateInterface;
use Vengine\Libs\traits\ContainerAwareTrait;
use Generator;

class InflectorAggregate implements InflectorAggregateInterface
{
    use ContainerAwareTrait;

    /**
     * @var Inflector[]
     */
    protected array $inflectors = [];

    public function add(string $type, ?callable $callback = null): Inflector
    {
        $inflector = new Inflector($type, callback: $callback);
        $this->inflectors[] = $inflector;

        return $inflector;
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function inflect($object)
    {
        /** @var Inflector $inflector */
        foreach ($this as $inflector) {
            $type = $inflector->getType();

            if ($object instanceof $type) {
                $inflector->setContainer($this->getContainer());
                $inflector->inflect($object);
            }
        }

        return $object;
    }

    public function getIterator(): Generator
    {
        yield from $this->inflectors;
    }
}
