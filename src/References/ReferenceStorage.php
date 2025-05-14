<?php

namespace Vengine\Libs\References;

use Vengine\Libs\Exceptions\ReferenceException;

class ReferenceStorage
{
    public function __construct(
        /** @var Reference[] $refs */
        protected array $refs = []
    ) { }

    public function addReference(Reference $reference): static
    {
        $this->refs[$reference->getReferenceId()] = $reference;

        return $this;
    }

    /**
     * @throws ReferenceException
     */
    public function addServiceInRef(string $service, string $referenceId): static
    {
        if (empty($this->refs[$referenceId])) {
            throw new ReferenceException(sprintf('reference "%s" not found', $referenceId));
        }

        $this->refs[$referenceId]->addService($service);

        return $this;
    }

    public function get(string $id): ?Reference
    {
        if (!empty($this->refs[$id])) {
            return $this->refs[$id];
        }

        return null;
    }

    /**
     * @return Reference[]
     */
    public function getRefs(): array
    {
        return $this->refs;
    }
}
