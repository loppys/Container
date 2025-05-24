<?php

namespace Vengine\Libs\DI\interfaces;

interface ContainerPackageInterface
{
    public function addPackage(PackageInterface $package): static;
    public function callPackage(string $name): mixed;
}
