<?php

namespace Vengine\Libs\Providers;

use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\interfaces\BootableServiceProviderInterface;
use Vengine\Libs\interfaces\ServiceProviderAggregateInterface;
use Vengine\Libs\interfaces\ServiceProviderInterface;
use Vengine\Libs\traits\ContainerAwareTrait;
use Generator;

class ServiceProviderAggregate implements ServiceProviderAggregateInterface
{
    use ContainerAwareTrait;

    /**
     * @var ServiceProviderInterface[]
     */
    protected array $providers = [];
    protected array $registered = [];

    /**
     * @throws ContainerException
     */
    public function add(ServiceProviderInterface $provider): ServiceProviderAggregateInterface
    {
        if (in_array($provider, $this->providers, true)) {
            return $this;
        }

        $provider->setContainer($this->getContainer());

        if ($provider instanceof BootableServiceProviderInterface) {
            $provider->boot();
        }

        $this->providers[] = $provider;

        return $this;
    }

    public function provides(string $id): bool
    {
        foreach ($this as $provider) {
            if ($provider->provides($id)) {
                return true;
            }
        }

        return false;
    }

    public function getIterator(): Generator
    {
        yield from $this->providers;
    }

    /**
     * @throws ContainerException
     */
    public function register(string $service): void
    {
        if (false === $this->provides($service)) {
            throw new ContainerException(
                sprintf('(%s) is not provided by a service provider', $service)
            );
        }

        /** @var ServiceProviderInterface $provider */
        foreach ($this as $provider) {
            if (in_array($provider->getId(), $this->registered, true)) {
                continue;
            }

            if ($provider->provides($service)) {
                $provider->register();
                $this->registered[] = $provider->getId();
            }
        }
    }
}
