<?php

namespace Vengine\Libs\DI\interfaces;

interface SingletonInterface
{
    public static function getInstance(): static;
}
