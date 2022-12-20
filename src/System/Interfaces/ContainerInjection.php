<?php

namespace Loader\System\Interfaces;

interface ContainerInjection
{
    public function getDefaultMethod(): string;

    public function getContainer(): ContainerInterface;
}
