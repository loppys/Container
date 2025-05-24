<?php

namespace Vengine\Libs\DI\interfaces;

interface ServiceProviderInterface
{
    public function getId(): string;
    public function provides(string $id): bool;
    public function register(): void;
    public function setId(string $id): static;
}
