<?php

namespace Loader\System;

use Loader\System\Helpers\Reflection;
use Loader\System\Interfaces\ContainerInjection;
use Loader\System\Interfaces\PackageInterface;

class Package implements PackageInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var string
     */
    protected $defaultMethod;

    /**
     * @var array
     */
    protected $defaultMethodArguments;

    /**
     * @var ContainerInjection
     */
    protected $object;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public function update(array $data): PackageInterface
    {
        $propertyList = get_object_vars($this);

        foreach ($propertyList as $property => $value) {
            if (property_exists($this, $property) && !empty($data[$property])) {
                $this->{$property} = $data[$property];
            }
        }

        Container::getInstance()->getStorage()->replace($this->getName(), $this);

        return $this;
    }

    public function getName(): string
    {
        return $this->name ?: '';
    }

    public function getClassName(): string
    {
        return $this->className ?: '';
    }

    public function getArguments(): array
    {
        return $this->arguments ?: [];
    }

    public function getDefaultMethod(): string
    {
        return $this->defaultMethod ?: '';
    }

    public function getDefaultMethodArguments(): array
    {
        return $this->defaultMethodArguments ?: [];
    }

    public function getObject(): ContainerInjection
    {
        return $this->object;
    }

    public function setName(string $name): PackageInterface
    {
        $this->name = $name;

        return $this;
    }

    public function setObject(ContainerInjection $object): PackageInterface
    {
        $this->object = $object;

        return $this;
    }

    public function setClassName(string $class): PackageInterface
    {
        $this->className = $class;

        return $this;
    }

    /**
     * @param array<mixed> $arguments
     */
    public function setArguments(array $arguments): PackageInterface
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function setDefaultMethod(string $method): PackageInterface
    {
        $this->defaultMethod = $method;

        return $this;
    }

    /**
     * @param array<mixed> $arguments
     */
    public function setDefaultMethodArguments(array $arguments): PackageInterface
    {
        $this->defaultMethodArguments = $arguments;

        return $this;
    }

    public function hasObject(): bool
    {
        return (!empty($this->object) && is_object($this->object));
    }
}
