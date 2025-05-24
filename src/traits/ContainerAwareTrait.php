<?php

namespace Vengine\Libs\DI\traits;

use Vengine\Libs\DI\Container;
use Vengine\Libs\DI\interfaces\ContainerInterface;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\interfaces\ContainerAwareInterface;

/**
 * @template T
 */
trait ContainerAwareTrait
{
    protected ContainerInterface|Container|null $container = null;

    /**
     * @throws ContainerException
     */
    public function setContainer(ContainerInterface $container): static
    {
        $this->container = $container;

        if ($this instanceof ContainerAwareInterface) {
            return $this;
        }

        throw new ContainerException(
            sprintf(
                'Attempt to use (%s) while not implementing (%s)',
                ContainerAwareTrait::class,
                ContainerAwareInterface::class
            )
        );
    }

    /**
     * @throws ContainerException
     */
    public function getContainer(): ContainerInterface|Container
    {
        if ($this->container instanceof ContainerInterface) {
            return $this->container;
        }

        throw new ContainerException('No container implementation has been set.');
    }
}
