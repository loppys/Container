<?php

namespace Vengine\Libs\traits;

use ReflectionFunctionAbstract;
use ReflectionNamedType;
use Vengine\Libs\Arguments\DefaultValueArgument;
use Vengine\Libs\Arguments\LiteralArgument;
use Vengine\Libs\Arguments\ResolvableArgument;
use Vengine\Libs\Container;
use Vengine\Libs\Exceptions\CircularServiceLoadingException;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\interfaces\ArgumentInterface;
use Vengine\Libs\interfaces\ContainerInterface;
use Vengine\Libs\interfaces\DefaultValueInterface;
use Vengine\Libs\interfaces\LiteralArgumentInterface;

trait ArgumentResolverTrait
{
    public function resolveArguments(array $arguments): array
    {
        try {
            $container = $this->getContainer();
        } catch (ContainerException $e) {
            $container = ($this instanceof Container) ? $this : null;
        }

        foreach ($arguments as &$arg) {
            if ($arg instanceof LiteralArgumentInterface) {
                $arg = $arg->getValue();

                continue;
            }

            if ($arg instanceof ArgumentInterface) {
                $argValue = $arg->getValue();
            } else {
                $argValue = $arg;
            }

            if (!is_string($argValue)) {
                continue;
            }

            if ($container instanceof ContainerInterface && $container->has($argValue)) {
                $arg = $container->get($argValue);

                if ($arg instanceof ArgumentInterface) {
                    $arg = $arg->getValue();
                }

                continue;
            }

            if ($arg instanceof DefaultValueInterface) {
                $arg = $arg->getDefaultValue();
            }
        }

        return $arguments;
    }

    /**
     * @throws NotFoundException
     */
    public function reflectArguments(ReflectionFunctionAbstract $method, array $args = []): array
    {
        $params = $method->getParameters();
        $arguments = [];

        foreach ($params as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $args)) {
                $arguments[] = new LiteralArgument($args[$name]);
                continue;
            }

            $type = $param->getType();

            if ($type instanceof ReflectionNamedType) {
                $typeHint = ltrim($type->getName(), '?');

                if ($param->isDefaultValueAvailable()) {
                    $arguments[] = new DefaultValueArgument($typeHint, $param->getDefaultValue());

                    continue;
                }

                $arguments[] = new ResolvableArgument($typeHint);

                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $arguments[] = new LiteralArgument($param->getDefaultValue());

                continue;
            }

            throw new NotFoundException(sprintf(
                'Unable to resolve a value for parameter (%s) in the function/method (%s)',
                $name,
                $method->getName()
            ));
        }

        return $this->resolveArguments($arguments);
    }

    abstract public function getContainer(): ContainerInterface;
}
