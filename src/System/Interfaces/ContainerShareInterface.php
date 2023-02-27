<?php

namespace Loader\System\Interfaces;

interface ContainerShareInterface
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getShared(string $name);

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return ContainerInterface
     */
    public function setShared(string $name, $value): ContainerInterface;
}
