<?php

namespace Vengine\Libs\Definitions;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Vengine\Libs\Arguments\LinkServiceArgument;
use Vengine\Libs\Arguments\LiteralArgument;
use Vengine\Libs\Exceptions\CircularServiceLoadingException;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\interfaces\ArgumentInterface;
use Vengine\Libs\interfaces\ContainerInterface;
use Vengine\Libs\interfaces\DefinitionInterface;
use Vengine\Libs\interfaces\LiteralArgumentInterface;
use Vengine\Libs\interfaces\ReplaceDefinitionInterface;
use Vengine\Libs\References\Reference;
use Vengine\Libs\Storage\ArgumentTypeStorage;
use Vengine\Libs\traits\ArgumentResolverTrait;
use Vengine\Libs\traits\ContainerAwareTrait;

class Definition implements DefinitionInterface
{
    use ArgumentResolverTrait;
    use ContainerAwareTrait;

    private array $loaded = [];

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
        /** @var Reference[] */
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
     * @throws ReflectionException
     * @throws CircularServiceLoadingException
     */
    public function resolve(): mixed
    {
        if (null !== $this->resolved && $this->isShared()) {
            return $this->resolved;
        }

        return $this->resolveNew();
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ReflectionException
     * @throws CircularServiceLoadingException
     */
    public function resolveNew(): mixed
    {
        $concrete = $this->concrete;

        if (is_callable($concrete)) {
            $concrete = $this->resolveCallable($concrete);
        }

        if ($concrete instanceof LiteralArgumentInterface) {
            $this->resolved = $concrete->getValue();

            return $concrete->getValue();
        }

        if ($concrete instanceof ArgumentInterface) {
            $concrete = $concrete->getValue();
        }

        if (is_string($concrete) && class_exists($concrete)) {
            $concrete = $this->resolveClass($concrete);
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
            $concrete = $container->get($concrete);
        }

        $this->resolved = $concrete;

        return $concrete;
    }

    /**
     * @throws CircularServiceLoadingException
     */
    protected function resolveCallable(callable $concrete): mixed
    {
        $resolved = $this->resolveArguments($this->arguments);

        return call_user_func_array($concrete, $resolved);
    }

    /**
     * @throws ReflectionException
     * @throws CircularServiceLoadingException
     */
    protected function resolveClass(string $concrete): object
    {
        $resolved = $this->resolveArguments($this->arguments);
        $reflection = new ReflectionClass($concrete);

        return $reflection->newInstanceArgs($resolved);
    }

    /**
     * @throws CircularServiceLoadingException
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

    public function clearLoaded(): void
    {
        $this->loaded = [];
    }
}
