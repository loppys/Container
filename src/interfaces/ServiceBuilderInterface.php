<?php

namespace Vengine\Libs\DI\interfaces;

use Vengine\Libs\DI\Container;
use Vengine\Libs\DI\Exceptions\CircularServiceLoadingException;

interface ServiceBuilderInterface
{
    /**
     * @throws CircularServiceLoadingException
     */
    public function build(string $id, Container $container): mixed;
}
