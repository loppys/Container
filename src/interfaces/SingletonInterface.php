<?php

namespace Vengine\Libs\interfaces;

interface SingletonInterface
{
    public static function getInstance(): static;
}
