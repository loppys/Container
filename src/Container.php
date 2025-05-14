<?php

namespace Vengine\Libs;

use Psr\Container\ContainerExceptionInterface;
use Vengine\Libs\config\ConfigResolver;
use Vengine\Libs\Definitions\Definition;
use Vengine\Libs\Definitions\DefinitionAggregate;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\interfaces\ConfigureInterface;
use Vengine\Libs\interfaces\ContainerAwareInterface;
use Vengine\Libs\interfaces\ContainerInterface;
use Vengine\Libs\interfaces\DefinitionAggregateInterface;
use Vengine\Libs\interfaces\InflectorAggregateInterface;
use Vengine\Libs\interfaces\InflectorInterface;
use Vengine\Libs\interfaces\ServiceCollectorInterface;
use Vengine\Libs\interfaces\ServiceProviderAggregateInterface;
use Vengine\Libs\Profiling\ProfilingEventHandler;
use Vengine\Libs\Profiling\ProfilingEventTypeStorage;
use Vengine\Libs\Profiling\TimerInterface;
use Vengine\Libs\Providers\ServiceProviderAggregate;
use Vengine\Libs\interfaces\DefinitionInterface;
use Vengine\Libs\interfaces\ServiceProviderInterface;
use Vengine\Libs\References\Reference;
use Vengine\Libs\ServiceCollectors\ArrayServiceCollector;
use Vengine\Libs\Settings\CacheSettings;
use Vengine\Libs\Settings\DefinitionSettings;
use Vengine\Libs\Settings\PackageSettings;
use Vengine\Libs\Settings\ProfilingSettings;
use Vengine\Libs\traits\ProfilingEventAwareTrait;
use Vengine\Libs\traits\SettingsAccessor;
use Psr\SimpleCache\CacheInterface;

/**
 * @template T
 */
class Container implements ContainerInterface
{
    use SettingsAccessor;
    use ProfilingEventAwareTrait;

    protected DefinitionAggregateInterface $definitions;
    protected ServiceProviderAggregateInterface $providers;
    protected InflectorAggregateInterface $inflectors;

    protected CacheInterface $cache;

    protected ?TimerInterface $timer = null;

    protected bool $defaultToShared = false;
    protected bool $defaultToOverwrite = false;

    protected string $packageAbstractClass = '';

    /**
     * @var ContainerInterface[]
     */
    protected array $delegates = [];

    /**
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __construct(
        ConfigureInterface $configure,
        ?DefinitionAggregateInterface $definitions = null,
        ?ServiceProviderAggregateInterface $providers = null,
        ?InflectorAggregateInterface $inflectors = null,
    ) {
        $configure = (new ConfigResolver())->configResolve($configure);

        $this->definitions = $definitions ?? new DefinitionAggregate();
        $this->providers = $providers ?? new ServiceProviderAggregate();
        $this->inflectors = $inflectors ?? new InflectorAggregate();

        $this->setProfilingEventHandler(new ProfilingEventHandler());

        $this->definitions->setContainer($this);
        $this->providers->setContainer($this);
        $this->inflectors->setContainer($this);

        if (!empty($configure['settings'])) {
            $this->setSettings($configure['settings']);
        }

        if (!empty($configure['services'])) {
            $this->collect(new ArrayServiceCollector($configure['services']));
        }

        $definitionsSettings = $this->getSettingsByName(DefinitionSettings::class);
        $cacheSettings = $this->getSettingsByName(CacheSettings::class);
        $packageSettings = $this->getSettingsByName(PackageSettings::class);
        $profilingSettings = $this->getSettingsByName(ProfilingSettings::class);

        $this->defaultToShared = $definitionsSettings->isAutoShared();
        $this->defaultToOverwrite = $definitionsSettings->isEnabledOverwrite();

        $cacheDriver = $cacheSettings->getCacheDriver();
        if ($cacheDriver) {
            $this->cache = $this->get($cacheDriver);
        }

        if ($packageSettings->isEnabled()) {
            $this->packageAbstractClass = $packageSettings->getAbstractClass();
        }

        [$timerService, $args] = $profilingSettings->getTimerConfig();

        $this->timer = $this->getWithArguments($timerService, $args, true);

        if ($profilingSettings->isEnabled()) {
            $this->profilingEventsRegister();
        }

        $this->profilingEventHandler->setEnabled($profilingSettings->isEnabled());
    }

    public function collect(ServiceCollectorInterface $collector): void
    {
        $collector->collect($this);
    }

    public function add(string $id, $concrete = null, bool $overwrite = false): DefinitionInterface
    {
        $toOverwrite = $this->defaultToOverwrite || $overwrite;
        $concrete = $concrete ?? $id;

        if (true === $this->defaultToShared) {
            return $this->addShared($id, $concrete, $toOverwrite);
        }

        return $this->definitions->add($id, $concrete, $toOverwrite);
    }

    public function addRawService(string $id, array $service): DefinitionInterface
    {
        $def = new Definition($id);

        $sharedTags = $definition['sharedTags'] ?? [];
        $refs = $definition['refs'] ?? [];
        $closure = $definition['closure'] ?? null;
        $class = $definition['class'] ?? '';
        $arguments = $definition['arguments'] ?? [];
        $calls = $definition['calls'] ?? [];
        $properties = $definition['properties'] ?? [];

        $def
            ->addSharedTags($sharedTags)
            ->addMethodCalls($calls)
            ->setConcrete($closure ?? $class)
            ->addArguments($arguments)
            ->replaceProperties($properties)
            ->addRefs($refs)
        ;

        return $def;
    }

    public function addShared(string $id, $concrete = null, bool $overwrite = false): DefinitionInterface
    {
        $toOverwrite = $this->defaultToOverwrite || $overwrite;
        $concrete = $concrete ??= $id;
        return $this->definitions->addShared($id, $concrete, $toOverwrite);
    }

    public function defaultToShared(bool $shared = true): ContainerInterface
    {
        $this->defaultToShared = $shared;

        return $this;
    }

    public function defaultToOverwrite(bool $overwrite = true): ContainerInterface
    {
        $this->defaultToOverwrite = $overwrite;
        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function extend(string $id): DefinitionInterface
    {
        if ($this->providers->provides($id)) {
            $this->providers->register($id);
        }

        if ($this->definitions->has($id)) {
            return $this->definitions->getDefinition($id);
        }

        throw new NotFoundException(sprintf(
            'Unable to extend alias (%s) as it is not being managed as a definition',
            $id
        ));
    }

    /**
     * @throws ContainerException
     */
    public function addServiceProvider(ServiceProviderInterface $provider): static
    {
        $this->providers->add($provider);

        return $this;
    }

    /**
     * @template TClass of object
     * @param class-string<TClass> $id
     * @return TClass
     *
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function get(string $id): mixed
    {
        return $this->resolve($id);
    }

    /**
     * @template TClass of object
     * @param class-string<TClass> $id
     * @return TClass
     *
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function getNew(string $id): mixed
    {
        return $this->resolve($id, [], true);
    }

    /**
     * @template TClass of object
     * @param class-string<TClass> $id
     * @return TClass
     *
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function getWithArguments(string $id, array $args = []): mixed
    {
        return $this->resolve($id, $args);
    }

    /**
     * @template TClass of object
     * @param class-string<TClass> $id
     * @return TClass
     *
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function getNewWithArguments(string $id, array $args = []): mixed
    {
        return $this->resolve($id, $args, true);
    }

    public function has(string $id): bool
    {
        if ($this->definitions->has($id)) {
            return true;
        }

        if ($this->definitions->hasTag($id)) {
            return true;
        }

        if ($this->providers->provides($id)) {
            return true;
        }

        foreach ($this->delegates as $delegate) {
            if ($delegate->has($id)) {
                return true;
            }
        }

        return false;
    }

    public function inflector(string $type, ?callable $callback = null): InflectorInterface
    {
        return $this->inflectors->add($type, $callback);
    }

    public function delegate(ContainerInterface $container): self
    {
        $this->delegates[] = $container;

        if ($container instanceof ContainerAwareInterface) {
            $container->setContainer($this);
        }

        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    protected function resolve(string $id, array $arguments = [], bool $new = false): mixed
    {
        $this->profilingEventHandler->handle(ProfilingEventTypeStorage::CREATE_SERVICE, $id);

        if ($this->definitions->has($id)) {
            $resolved = (true === $new) ? $this->definitions->resolveNew($id) : $this->definitions->resolve($id);

            $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

            return $this->inflectors->inflect($resolved);
        }

        if ($this->definitions->hasTag($id)) {
            $arrayOf = (true === $new)
                ? $this->definitions->resolveTaggedNew($id)
                : $this->definitions->resolveTagged($id);

            array_walk($arrayOf, function (&$resolved) {
                $resolved = $this->inflectors->inflect($resolved);
            });

            $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

            return $arrayOf;
        }

        if ($this->providers->provides($id)) {
            $this->providers->register($id);

            if (false === $this->definitions->has($id) && false === $this->definitions->hasTag($id)) {
                throw new ContainerException(sprintf('Service provider lied about providing (%s) service', $id));
            }

            $resolved = $this->resolve($id, $new);

            $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

            return $resolved;
        }

        foreach ($this->delegates as $delegate) {
            if ($delegate->has($id)) {
                $resolved = $delegate->get($id);

                $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

                return $this->inflectors->inflect($resolved);
            }
        }

        throw new NotFoundException(sprintf('Alias (%s) is not being managed by the container or delegates', $id));
    }

    private function profilingEventsRegister(): void
    {
        $this->profilingEventHandler->addEvent(
            'create.service',
            ProfilingEventTypeStorage::CREATE_SERVICE,
            function (string $name, string $id = null) {
                $this->timer->addPoint($name, $id);
            },
            function (string $name, string $id = null) {
                return ["{$name}.{$id}", $id];
            }
        );

        $this->profilingEventHandler->addEvent(
            'create.service',
            ProfilingEventTypeStorage::END_SERVICE_CREATION,
            function (string $name, ?string $id = null) {
                $this->timer->endPoint($name);
            },
            function (string $name, ?string $id = null) {
                return ["{$name}.{$id}", $id];
            }
        );

        $this->profilingEventHandler->addEvent(
            'create.definition',
            ProfilingEventTypeStorage::CREATE_DEFINITION,
            function (string $name, mixed $data = null) {
                $this->timer->addPoint($name, $data);
            },
            function (string $name, string $id = null) {
                return ["{$name}.{$id}", $id];
            }
        );

        $this->profilingEventHandler->addEvent(
            'create.argument',
            ProfilingEventTypeStorage::CREATE_ARGUMENT,
            function (string $name, mixed $data = null) {
                $this->timer->addPoint($name, $data);
            },
            function (string $name, string $argument = null) {
                return ["{$name}.{$argument}", $argument];
            }
        );

        $this->profilingEventHandler->addEvent(
            'create.argument',
            ProfilingEventTypeStorage::END_ARGUMENT_CREATION,
            function (string $name, mixed $data = null) {
                $this->timer->endPoint($name);
            },
            function (string $name, string $argument = null) {
                return ["{$name}.{$argument}", $argument];
            }
        );

        $this->profilingEventHandler->addEvent(
            'create.definition',
            ProfilingEventTypeStorage::END_DEFINITION_CREATION,
            function (string $name, mixed $data = null) {
                $this->timer->endPoint($name);
            },
            function (string $name, string $id = null) {
                return ["{$name}.{$id}", $id];
            }
        );
    }
}
