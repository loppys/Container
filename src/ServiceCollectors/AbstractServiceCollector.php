<?php

namespace Vengine\Libs\ServiceCollectors;

use Vengine\Libs\Container;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\interfaces\ServiceCollectorInterface;

abstract class AbstractServiceCollector implements ServiceCollectorInterface
{
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
