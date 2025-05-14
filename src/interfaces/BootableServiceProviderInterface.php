<?php

namespace Vengine\Libs\interfaces;

interface BootableServiceProviderInterface extends ServiceProviderInterface
{
    public function boot(): void;
}
