<?php

namespace Vengine\Libs\traits;

use Vengine\Libs\Container;
use Psr\Container\ContainerInterface;

/**
 * @template T
 */
trait ContainerAwareTrait
{
    protected ContainerInterface|Container $container;

    public function setContainer(ContainerInterface $container): static
    {
        $this->container = $container;

        return $this;
    }

    public function getContainer(): ContainerInterface|Container
    {
        return $this->container;
    }
}
