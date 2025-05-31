<?php

namespace Vengine\Libs\DI;

use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Vengine\Libs\DI\Arguments\LinkServiceArgument;
use Vengine\Libs\DI\config\ConfigResolver;
use Vengine\Libs\DI\Definitions\Definition;
use Vengine\Libs\DI\Definitions\DefinitionAggregate;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use Vengine\Libs\DI\interfaces\CollectorContainerInterface;
use Vengine\Libs\DI\interfaces\ConfigureInterface;
use Vengine\Libs\DI\interfaces\ContainerAwareInterface;
use Vengine\Libs\DI\interfaces\ContainerInterface;
use Vengine\Libs\DI\interfaces\DefinitionAggregateInterface;
use Vengine\Libs\DI\interfaces\InflectorAggregateInterface;
use Vengine\Libs\DI\interfaces\InflectorInterface;
use Vengine\Libs\DI\interfaces\PackageInterface;
use Vengine\Libs\DI\interfaces\ServiceCollectorInterface;
use Vengine\Libs\DI\interfaces\ServiceProviderAggregateInterface;
use Vengine\Libs\DI\Profiling\ProfilingEventHandler;
use Vengine\Libs\DI\Profiling\ProfilingEventTypeStorage;
use Vengine\Libs\DI\Profiling\TimerInterface;
use Vengine\Libs\DI\Providers\ServiceProviderAggregate;
use Vengine\Libs\DI\interfaces\DefinitionInterface;
use Vengine\Libs\DI\interfaces\ServiceProviderInterface;
use Vengine\Libs\DI\ServiceCollectors\ArrayServiceCollector;
use Vengine\Libs\DI\Settings\DefinitionSettings;
use Vengine\Libs\DI\Settings\PackageSettings;
use Vengine\Libs\DI\Settings\ProfilingSettings;
use Vengine\Libs\DI\traits\ProfilingEventAwareTrait;
use Vengine\Libs\DI\traits\SettingsAccessor;
use Psr\SimpleCache\CacheInterface;

/**
 * @template T
 */
class Container implements ContainerInterface, CollectorContainerInterface
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

    protected bool $packageEnabled = false;
    /** @var PackageInterface[] */
    protected array $packages;

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
        $this->definitions = $definitions ?? new DefinitionAggregate();
        $this->providers = $providers ?? new ServiceProviderAggregate();
        $this->inflectors = $inflectors ?? new InflectorAggregate();

        $this->definitions->setContainer($this);
        $this->providers->setContainer($this);
        $this->inflectors->setContainer($this);

        $this->setProfilingEventHandler(new ProfilingEventHandler());

        $configure = (new ConfigResolver())->configResolve($configure);

        if (!empty($configure['settings'])) {
            $this->setSettings($configure['settings']);
        }

        $definitionsSettings = $this->getSettingsByName(DefinitionSettings::class);
        $packageSettings = $this->getSettingsByName(PackageSettings::class);
        $profilingSettings = $this->getSettingsByName(ProfilingSettings::class);

        if (!empty($configure['services'])) {
            $this->collect(new ArrayServiceCollector($configure['services']));
        }

        $this->defaultToShared = $definitionsSettings->isAutoShared();
        $this->defaultToOverwrite = $definitionsSettings->isEnabledOverwrite();

        if ($packageSettings->isEnabled()) {
            $this->packageEnabled = $packageSettings->isEnabled();
        }

        [$timerService, $args] = $profilingSettings->getTimerConfig();

        $this->timer = $this->getWithArguments($timerService, $args);

        if ($profilingSettings->isEnabled()) {
            $this->profilingEventsRegister();
        }

        $this->profilingEventHandler->setEnabled($profilingSettings->isEnabled());
    }

    public function callPackage(string $name): mixed
    {
        if ($this->packageEnabled === false) {
            return null;
        }

        if (empty($this->packages[$name])) {
            return null;
        }

        return $this->packages[$name]->call($this);
    }

    public function addPackage(PackageInterface $package): static
    {
        if ($this->packageEnabled === false) {
            return $this;
        }

        foreach ($package->getCollectors() as $collector) {
            $this->collect($collector);
        }

        $this->packages[$package->getName()] = $package;

        return $this;
    }

    public function collect(ServiceCollectorInterface $collector): void
    {
        $collector->collect($this);
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function add(string $id, $concrete = null, bool $overwrite = false): DefinitionInterface
    {
        $toOverwrite = $this->defaultToOverwrite || $overwrite;
        $concrete = $concrete ?? $id;

        if (true === $this->defaultToShared) {
            return $this->addShared($id, $concrete, $toOverwrite);
        }

        return $this->definitions->add($id, $concrete, $toOverwrite);
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function addRawService(string $id, array $service): DefinitionInterface
    {
        if (str_contains($id, '@@')) {
            $id = mb_substr($id, 2);
            $this->definitions->replace($id, $service);

            return $this->definitions->getDefinition($id);
        }

        $def = new Definition($id);

        $def->setContainer($this);
        $def->fetchConstructor();

        $sharedTags = $service['sharedTags'] ?? [];
        $refs = $service['refs'] ?? [];
        $closure = $service['closure'] ?? null;
        $class = $service['class'] ?? null;
        $arguments = $service['arguments'] ?? [];
        $calls = $service['calls'] ?? [];
        $properties = $service['properties'] ?? [];

        if (is_null($closure) && is_null($class)) {
            throw new ContainerException('addRawService: closure && class is null');
        }

        foreach ($arguments as $ak => &$av) {
            if (!is_string($av)) {
                continue;
            }

            if (str_contains($av, '@')) {
                $av = mb_substr($av, 1);

                $av = (new LinkServiceArgument())
                    ->setId($av)
                ;
            }
        }

        foreach ($properties as $pk => &$pv) {
            if (str_contains($pv, '@')) {
                $pv = mb_substr($pv, 1);

                $pv = (new LinkServiceArgument())
                    ->setId($pv)
                ;
            }
        }

        $def
            ->addSharedTags($sharedTags)
            ->addMethodCalls($calls)
            ->setConcrete($closure ?? $class)
            ->addArguments($arguments)
            ->replaceProperties($properties)
            ->addRefs($refs)
            ->setShared(
                $service['shared']
                    ?? $this->getSettingsByName(DefinitionSettings::class)?->isAutoShared()
                    ?? false
            )
        ;

        $this->definitions->add($id, $def);

        return $def;
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function addShared(string $id, $concrete = null, bool $overwrite = false): DefinitionInterface
    {
        $toOverwrite = $this->defaultToOverwrite || $overwrite;
        $concrete ??= $id;

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
    public function getDefinition(string $id): DefinitionInterface
    {
        return $this->definitions->getDefinition($id);
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
        if (str_contains($id, '@')) {
            $id = mb_substr($id, 1);
        }

        if ($this->definitions->has($id)) {
            return true;
        }

        if ($this->definitions->hasSharedTag($id)) {
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
        if (str_contains($id, '@')) {
            $id = mb_substr($id, 1);
        }

        $this->profilingEventHandler->handle(ProfilingEventTypeStorage::CREATE_SERVICE, $id);

        if ($this->definitions->has($id)) {
            $resolved = (true === $new)
                ? $this->definitions->resolveNew($id, $arguments)
                : $this->definitions->resolve($id, $arguments);

            $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

            return $this->inflectors->inflect($resolved);
        }

        if ($this->definitions->hasSharedTag($id)) {
            $arrayOf = (true === $new)
                ? $this->definitions->resolveTaggedNew($id, $arguments)
                : $this->definitions->resolveTagged($id, $arguments);

            array_walk($arrayOf, function (&$resolved) {
                $resolved = $this->inflectors->inflect($resolved);
            });

            $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

            return count($arrayOf) < 2 ? $arrayOf[0] : $arrayOf;
        }

        if ($this->providers->provides($id)) {
            $this->providers->register($id);

            if (false === $this->definitions->has($id) && false === $this->definitions->hasSharedTag($id)) {
                throw new ContainerException(sprintf('Service provider lied about providing (%s) service', $id));
            }

            $resolved = $this->resolve($id, $arguments, $new);

            $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

            return $resolved;
        }

        foreach ($this->delegates as $delegate) {
            if ($delegate->has($id)) {
                if (!empty($arguments)) {
                    $resolved = $delegate->getWithArguments($id, $arguments);
                } else {
                    $resolved = $delegate->get($id);
                }

                $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

                return $this->inflectors->inflect($resolved);
            }
        }

        if (class_exists($id)) {
            $this->addRawService($id, [
                'class' => $id,
                'arguments' => $arguments,
                'shared' => $this->getSettingsByName(DefinitionSettings::class)->isAutoShared()
            ]);

            $resolved = (true === $new)
                ? $this->definitions->resolveNew($id)
                : $this->definitions->resolve($id);

            $this->profilingEventHandler->handle(ProfilingEventTypeStorage::END_SERVICE_CREATION, $id);

            return $this->inflectors->inflect($resolved);
        }

        throw new NotFoundException(sprintf('Service (%s) is not being managed by the container or delegates', $id));
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
