<?php

namespace Vengine\Libs\DI\interfaces;

interface ContainerAwareInterface
{
    public function getContainer(): ContainerInterface;
    public function setContainer(ContainerInterface $container): static;
}
