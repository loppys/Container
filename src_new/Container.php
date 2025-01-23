<?php

namespace Vengine\Libs;

use Vengine\Libs\Builders\ServiceBuilder;
use Psr\Container\ContainerInterface;
use ReflectionException;
use RuntimeException;

/**
 * @template T
 */
class Container implements ContainerInterface
{
    protected ServiceBuilder $serviceBuilder;

    protected static Container $_instance;

    public function __construct(array $config = [], ?ServiceBuilder $serviceBuilder = null)
    {
        if (!empty(self::$_instance)) {
            throw new RuntimeException('Container is singleton. use getInstance()');
        }

        $this->serviceBuilder = $serviceBuilder ?? new ServiceBuilder();

        $this->setConfig($config);

        self::$_instance = $this;
    }

    public static function getInstance(): Container
    {
        if (empty(self::$_instance)) {
            throw new RuntimeException('Container not init.');
        }

        return self::$_instance;
    }

    public function getCurrentConfig(): array
    {
        return $this->serviceBuilder->getConfig();
    }

    protected function aliasRegister(string $reference, string $alias): void
    {
        $this->serviceBuilder->getAliasFinder()->registerAlias($reference, $alias);
    }

    protected function setConfig(array $config): static
    {
        foreach ($config['services'] ?? [] as $service => $replacedService) {
            if (!is_string($replacedService) || !is_string($service)) {
                continue;
            }

            if ((class_exists($service) || interface_exists($service)) && class_exists($replacedService)) {
                $this->aliasRegister($service, $replacedService);
            }
        }

        $this->serviceBuilder->setConfig($config);

        return $this;
    }

    /**
     * @template TNew of object
     * @param class-string<TNew> $id
     * @return TNew
     *
     * @throws ReflectionException
     */
    public function get(string $id): mixed
    {
        return $this->serviceBuilder->createService($id);
    }

    /**
     * @template TClass of object
     * @param class-string<TClass> $id
     * @return TClass
     *
     * @throws ReflectionException
     */
    public function getNew(string $id): mixed
    {
        return $this->serviceBuilder->createNewService($id);
    }

    public function has(string $id): bool
    {
        return $this->serviceBuilder->hasService($id);
    }

    public function add(string $id, array $config): bool
    {
        return $this->serviceBuilder->addService($id, $config);
    }

    public function changeConfig(string $config): void
    {
        $this->serviceBuilder->changeConfig($config);
    }

    public function registerAlias(string $reference, string $alias): void
    {
        $this->serviceBuilder->registerAlias($reference, $alias);
    }
}
