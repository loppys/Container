<?php

namespace Vengine\Libs\DI\Providers;

use Vengine\Libs\DI\interfaces\ServiceProviderInterface;
use Vengine\Libs\DI\traits\ContainerAwareTrait;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    use ContainerAwareTrait;

    protected string $id;

    public function getId(): string
    {
        if (empty($this->id)) {
            $this->id = get_class($this);
        }

        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }
}
