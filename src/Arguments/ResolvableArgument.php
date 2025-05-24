<?php

namespace Vengine\Libs\DI\Arguments;

use Vengine\Libs\DI\interfaces\ResolvableArgumentInterface;

class ResolvableArgument implements ResolvableArgumentInterface
{
    protected ?string $id;

    public function __construct(protected string $value)
    {
    }

    public function getValue(): string
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
