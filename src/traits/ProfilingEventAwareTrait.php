<?php

namespace Vengine\Libs\DI\traits;

use Vengine\Libs\DI\Profiling\ProfilingEventHandler;

trait ProfilingEventAwareTrait
{
    protected ?ProfilingEventHandler $profilingEventHandler = null;

    public function setProfilingEventHandler(ProfilingEventHandler $profilingEventHandler): static
    {
        $this->profilingEventHandler = $profilingEventHandler;

        return $this;
    }

    public function getProfilingEventHandler(): ?ProfilingEventHandler
    {
        return $this->profilingEventHandler;
    }
}
