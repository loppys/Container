<?php

namespace Vengine\Libs\Packages;

use Vengine\Libs\interfaces\ContainerInterface;
use Vengine\Libs\interfaces\PackageInterface;
use Vengine\Libs\interfaces\ServiceCollectorInterface;

abstract class AbstractPackage implements PackageInterface
{
    protected string $name;

    /** @var ServiceCollectorInterface[] $collectors */
    protected array $collectors = [];

    abstract public function call(ContainerInterface $container): mixed;

    public function addServiceCollector(ServiceCollectorInterface $serviceCollector): static
    {
        $this->collectors[] = $serviceCollector;

        return $this;
    }

    /**
     * @return ServiceCollectorInterface[]
     */
    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
