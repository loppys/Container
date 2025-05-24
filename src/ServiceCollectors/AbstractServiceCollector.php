<?php

namespace Vengine\Libs\DI\ServiceCollectors;

use Vengine\Libs\DI\Container;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\interfaces\DefinitionInterface;
use Vengine\Libs\DI\interfaces\ServiceCollectorInterface;

abstract class AbstractServiceCollector implements ServiceCollectorInterface
{
    /**
     * @var DefinitionInterface[]
     */
    protected array $definitions = [];

    /**
     * @throws ContainerException
     */
    public function collect(Container $container): void
    {
        if (empty($this->definitions)) {
            throw new ContainerException("empty definitions");
        }

        $this->delegateCollect($container);
    }

    abstract protected function delegateCollect(Container $container): void;
}
