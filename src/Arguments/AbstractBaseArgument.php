<?php

namespace Vengine\Libs\DI\Arguments;

use Vengine\Libs\DI\interfaces\ArgumentInterface;

abstract class AbstractBaseArgument implements ArgumentInterface
{
    protected ?string $id = null;

    protected mixed $value = null;

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
