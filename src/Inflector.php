<?php

namespace Vengine\Libs\DI;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use Vengine\Libs\DI\interfaces\InflectorInterface;
use Vengine\Libs\DI\traits\ArgumentResolverTrait;
use Vengine\Libs\DI\traits\ContainerAwareTrait;
use Vengine\Libs\DI\interfaces\ArgumentResolverInterface;

class Inflector implements InflectorInterface, ArgumentResolverInterface
{
    use ArgumentResolverTrait;
    use ContainerAwareTrait;

    /**
     * @var callable|null
     */
    protected $callback;

    protected array $inflected = [];

    public function __construct(
        protected string $type,
        ?callable $callback = null,
        protected bool $oncePerMatch = false,
        protected array $methods = [],
        protected array $properties = [],
    ) {
        $this->callback = $callback;
    }

    public function oncePerMatch(): InflectorInterface
    {
        $this->oncePerMatch = true;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function invokeMethod(string $name, array $args): InflectorInterface
    {
        $this->methods[$name] = $args;
        return $this;
    }

    public function invokeMethods(array $methods): InflectorInterface
    {
        foreach ($methods as $name => $args) {
            $this->invokeMethod($name, $args);
        }

        return $this;
    }

    public function setProperty(string $property, $value): InflectorInterface
    {
        $this->properties[$property] = $this->resolveArguments([$value])[0];
        return $this;
    }

    public function setProperties(array $properties): InflectorInterface
    {
        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value);
        }

        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function inflect(object $object): void
    {
        if (true === $this->oncePerMatch && in_array($object, $this->inflected, true)) {
            return;
        }

        $properties = $this->resolveArguments(array_values($this->properties));
        $properties = array_combine(array_keys($this->properties), $properties);

        foreach ($properties ?: [] as $property => $value) {
            $object->{$property} = $value;
        }

        foreach ($this->methods as $method => $args) {
            $args = $this->resolveArguments($args);
            $callable = [$object, $method];
            call_user_func_array($callable, $args);
        }

        if ($this->callback !== null) {
            call_user_func($this->callback, $object);
        }

        if (true === $this->oncePerMatch) {
            $this->inflected[] = $object;
        }
    }
}
