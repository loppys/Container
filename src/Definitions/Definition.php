<?php

namespace Vengine\Libs\DI\Definitions;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Vengine\Libs\DI\Arguments\LinkServiceArgument;
use Vengine\Libs\DI\Arguments\LiteralArgument;
use Vengine\Libs\DI\Exceptions\CircularServiceLoadingException;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use Vengine\Libs\DI\interfaces\ArgumentInterface;
use Vengine\Libs\DI\interfaces\ContainerInterface;
use Vengine\Libs\DI\interfaces\DefinitionInterface;
use Vengine\Libs\DI\interfaces\LiteralArgumentInterface;
use Vengine\Libs\DI\Storage\ArgumentTypeStorage;
use Vengine\Libs\DI\traits\ArgumentResolverTrait;
use Vengine\Libs\DI\traits\ContainerAwareTrait;

class Definition implements DefinitionInterface
{
    use ArgumentResolverTrait;
    use ContainerAwareTrait;

    private bool $constructorFetched = false;

    protected mixed $resolved = null;
    protected array $recursiveCheck = [];
    protected array $replaceProperties = [];

    public function __construct(
        protected string $id,
        protected mixed $concrete = null,
        protected bool $shared = false,
        /** @var ArgumentInterface[] */
        protected array $arguments = [],
        protected array $methods = [],
        protected array $sharedTags = [],
        protected array $refs = []
    ) {
        $this->setId($this->id);
        $this->concrete ??= $this->id;
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function fetchConstructor(): void
    {
        if (
            !is_callable($this->concrete)
            && class_exists($this->concrete)
            && method_exists($this->concrete, '__construct')
        ) {
            $rfm = (new ReflectionClass($this->concrete))->getConstructor();

            $this->arguments = $this->reflectArguments($rfm, $this->arguments);
        }
    }

    public function replaceKeys(array $values): DefinitionInterface
    {
        $objProperties = get_object_vars($this);
        foreach ($values as $key => $value) {
            if (in_array($key, ['class', 'closure'], true)) {
                $key = 'concrete';
            }

            if (!array_key_exists($key, $objProperties)) {
                continue;
            }

            if ($key === 'concrete') {
                $this->setConcrete($value);

                continue;
            }

            $this->{$key} = $value;
        }

        $this->resolved = null;

        return $this;
    }

    public function addSharedTags(array $tags): DefinitionInterface
    {
        foreach ($tags as $tag) {
            $this->sharedTags[$tag] = true;
        }

        return $this;
    }

    public function hasSharedTag(string $tag): bool
    {
        return isset($this->sharedTags[$tag]);
    }

    public function setId(string $id): DefinitionInterface
    {
        $this->id = static::normaliseAlias($id);

        return $this;
    }

    public function getId(): string
    {
        return static::normaliseAlias($this->id);
    }

    public function setShared(bool $shared = true): DefinitionInterface
    {
        $this->shared = $shared;

        return $this;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function getConcrete(): mixed
    {
        return $this->concrete;
    }

    public function setConcrete(mixed $concrete): DefinitionInterface
    {
        $this->concrete = $concrete;
        $this->resolved = null;

        return $this;
    }

    public function addRawArgument(mixed $value, ?string $name = null): DefinitionInterface
    {
        $type = gettype($value);
        if (in_array($type, ArgumentTypeStorage::getList(), true)) {
            $arg = new LiteralArgument($value, $type);

            if (!is_null($name)) {
                $arg->setId($name);
            }

            $this->addArgument($arg);

            return $this;
        }

        if (str_contains($value, '@')) {
            $value = mb_substr($value, 1);
            if ($this->getContainer()->has($value)) {
                $this->addArgument(
                    (new LinkServiceArgument())
                        ->setId($value)
                );
            }
        }

        return $this;
    }

    public function addArgument(ArgumentInterface $arg): DefinitionInterface
    {
        $this->arguments[] = $arg;

        return $this;
    }

    public function addArguments(array $args): DefinitionInterface
    {
        foreach ($args as $arg) {
            if (!$arg instanceof ArgumentInterface) {
                $this->addRawArgument($arg);
            } else {
                $this->addArgument($arg);
            }
        }

        return $this;
    }

    public function addMethodCall(string $method, array $args = []): DefinitionInterface
    {
        $this->methods[] = [
            'method' => $method,
            'arguments' => $args
        ];

        return $this;
    }

    public function addMethodCalls(array $methods = []): DefinitionInterface
    {
        foreach ($methods as $method => $args) {
            $this->addMethodCall($method, $args);
        }

        return $this;
    }

    public function replaceProperties(array $properties = []): DefinitionInterface
    {
        foreach ($properties as $property => $value) {
            $this->replaceProperties[$property] = $value;
        }

        return $this;
    }

    public function addRefs(array ...$refs): DefinitionInterface
    {
        $this->refs = $refs;

        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws CircularServiceLoadingException
     */
    public function resolve(array $arguments = []): mixed
    {
        if (null !== $this->resolved && $this->isShared()) {
            return $this->resolved;
        }

        return $this->resolveNew($arguments);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ReflectionException
     * @throws CircularServiceLoadingException
     */
    public function resolveNew(array $arguments = []): mixed
    {
        $concrete = $this->concrete;

        if ($this->constructorFetched === false) {
            $this->fetchConstructor();
        }

        if (is_callable($concrete)) {
            $concrete = $this->resolveCallable($concrete, $arguments);
        }

        if ($concrete instanceof LiteralArgumentInterface) {
            $this->resolved = $concrete->getValue();

            return $concrete->getValue();
        }

        if ($concrete instanceof ArgumentInterface) {
            $concrete = $concrete->getValue();
        }

        if (is_string($concrete) && class_exists($concrete)) {
            $concrete = $this->resolveClass($concrete, $arguments);
        }

        if (is_object($concrete)) {
            $concrete = $this->resolveProperties($concrete, $this->replaceProperties);
            $concrete = $this->invokeMethods($concrete);
        }

        try {
            $container = $this->getContainer();
        } catch (ContainerException $e) {
            $container = null;
        }

        if (is_string($concrete) && in_array($concrete, $this->recursiveCheck)) {
            $this->resolved = $concrete;

            return $concrete;
        }

        if (is_string($concrete) && $container instanceof ContainerInterface && $container->has($concrete)) {
            $this->recursiveCheck[] = $concrete;
            if (!empty($arguments)) {
                $concrete = $container->getWithArguments($concrete, $arguments);
            } else {
                $concrete = $container->get($concrete);
            }
        }
        
        if ($concrete instanceof ContainerAwareInterface && !is_null($container)) {
            $concrete->setContainer($container);
        }

        $this->resolved = $concrete;

        return $concrete;
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function resolveCallable(callable $concrete, array $arguments = []): mixed
    {
        if (!empty($arguments)) {
            $arguments = array_merge_recursive($this->arguments, $arguments);
        } else {
            $arguments = $this->arguments;
        }

        if (empty($arguments)) {
            $arguments = $this->reflectArguments(new ReflectionFunction($concrete));
        }

        $resolved = $this->resolveArguments($arguments);

        return call_user_func_array($concrete, $resolved);
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function resolveClass(string $concrete, array $arguments = []): object
    {
        if (!empty($arguments)) {
            $arguments = array_merge_recursive($this->arguments, $arguments);
        } else {
            $arguments = $this->arguments;
        }

        $reflection = new ReflectionClass($concrete);
        $construct = $reflection->getConstructor();
        foreach ($arguments as $name => $argument) {
            if (is_string($argument)) {
                if (str_contains($argument, '@') && $this->getContainer()->has($argument)) {
                    $argument = mb_substr($argument, 1);

                    $arguments[$name] = $this->getContainer()->get($argument);

                    continue;
                }
            }

            if (is_null($construct)) {
                continue;
            }

            $argument = $this->reflectArguments($construct, [$argument]);
            $arguments[$name] = array_shift($argument);
        }

        $resolved = $this->resolveArguments($arguments);

        return $reflection->newInstanceArgs($resolved);
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    protected function invokeMethods(object $instance): object
    {
        foreach ($this->methods as $method) {
            $args = $this->resolveArguments($method['arguments']);
            $callable = [$instance, $method['method']];

            call_user_func_array($callable, $args);
        }

        return $instance;
    }

    public static function normaliseAlias(string $alias): string
    {
        return ltrim($alias, "\\");
    }

    public function clearResolved(): void
    {
        $this->resolved = null;
    }
}
