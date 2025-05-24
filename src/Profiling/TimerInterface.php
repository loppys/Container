<?php

namespace Vengine\Libs\DI\Profiling;

interface TimerInterface
{
    public function addPoint(string $point, mixed $data = null): static;
    public function endPoint(string $point): static;
}
