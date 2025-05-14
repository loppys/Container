<?php

namespace Vengine\Libs\References;

use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\interfaces\ContainerAwareInterface;
use Vengine\Libs\traits\ContainerAwareTrait;

class ReferenceFinder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct(
        protected readonly ReferenceStorage $storage
    ) {

    }

    /**
     * @throws ContainerException
     */
    public function findService(string $referenceId): ?string
    {
        $ref = $this->storage->get($referenceId);

        if (is_null($ref)) {
            return null;
        }

        $services = $ref->getServices();

        if (empty($services)) {
            return null;
        }

        foreach ($services as $service) {
            if ($this->getContainer()->serviceCreated($service)) {
                return $service;
            }
        }

        return $services[0];
    }
}
