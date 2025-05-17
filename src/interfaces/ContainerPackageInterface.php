<?php

namespace Vengine\Libs\interfaces;

interface ContainerPackageInterface
{
    public function addPackage(PackageInterface $package): static;
    public function callPackage(string $name): mixed;
}
