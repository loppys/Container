<?php

namespace Vengine\Libs\traits;

use Vengine\Libs\Profiling\ProfilingEventHandler;

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
