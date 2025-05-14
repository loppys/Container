<?php

namespace Vengine\Libs\Packages;

use Vengine\Libs\interfaces\ServiceCollectorInterface;

abstract class AbstractPackage
{
    public function __construct(
        protected string $name,
        /** @var ServiceCollectorInterface[] $collectors */
        protected array $collectors = []
    )
    { }

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
