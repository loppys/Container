<?php

namespace Vengine\Libs\interfaces;

use Vengine\Libs\Container;
use Vengine\Libs\Exceptions\CircularServiceLoadingException;

interface ServiceBuilderInterface
{
    /**
     * @throws CircularServiceLoadingException
     */
    public function build(string $id, Container $container): mixed;
}
