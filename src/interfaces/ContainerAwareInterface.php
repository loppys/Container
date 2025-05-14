<?php

namespace Vengine\Libs\interfaces;

interface ContainerAwareInterface
{
    public function getContainer(): ContainerInterface;
    public function setContainer(ContainerInterface $container): static;
}
