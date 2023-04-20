<?php

namespace Loader\System\Interfaces;

interface PackageAdapterInterface
{
    public function addPackage(PackageInterface $package): ContainerInterface;

    public function getPackage(string $name): PackageInterface;

    public function newPackage(string $name): PackageInterface;
}
