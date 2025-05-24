<?php

namespace Vengine\Libs\DI\interfaces;

interface PackageInterface
{
    public function addServiceCollector(ServiceCollectorInterface $serviceCollector): static;

    /**
     * @return ServiceCollectorInterface[]
     */
    public function getCollectors(): array;

    public function getName(): string;

    public function call(ContainerInterface $container): mixed;
}
