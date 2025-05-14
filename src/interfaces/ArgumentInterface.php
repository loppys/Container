<?php

namespace Vengine\Libs\interfaces;

interface ArgumentInterface
{
    public function getValue(): mixed;

    public function setId(string $id): static;
    public function getId(): ?string;
}
