<?php

namespace Vengine\Libs\DI\interfaces;

interface BootableServiceProviderInterface extends ServiceProviderInterface
{
    public function boot(): void;
}
