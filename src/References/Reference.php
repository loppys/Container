<?php

namespace Vengine\Libs\References;

class Reference
{
    public function __construct(
        protected readonly string $referenceId,
        /** @var string[] */
        protected array $services = []
    ) {
    }

    public function addService(string $serviceId): static
    {
        if (in_array($serviceId, $this->services, true)) {
            return $this;
        }

        $this->services[] = $serviceId;

        return $this;
    }

    public function getServices(): array
    {
        return $this->services;
    }

    public function getReferenceId(): string
    {
        return $this->referenceId;
    }
}
