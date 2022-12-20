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
        $propertyList = Reflection::getObjectVars($this);

        foreach ($propertyList as $property) {
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setObject(ContainerInjection $object): void
    {
        $this->object = $object;
    }

    public function setClassName(string $class): void
    {
        $this->className = $class;
    }

    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function setDefaultMethod(string $method): void
    {
        $this->defaultMethod = $method;
    }

    public function setDefaultMethodArguments(array $arguments): void
    {
        $this->defaultMethodArguments = $arguments;
    }

    public function hasObject(): bool
    {
        return (!empty($this->object) && is_object($this->object));
    }
}
