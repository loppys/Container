<?php

namespace Vengine\Libs\DI\interfaces;

interface ServiceProviderAggregateInterface extends AggregateInterface
{
    public function add(ServiceProviderInterface $provider): ServiceProviderAggregateInterface;
    public function provides(string $id): bool;
    public function register(string $service): void;
}
