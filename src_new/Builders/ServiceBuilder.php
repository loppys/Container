<?php

namespace Vengine\Libs\Builders;

use Vengine\Libs\Aliases\AliasFinder;
use ReflectionException;
use ReflectionClass;
use RuntimeException;
use ReflectionFunction;

/**
 * @template T
 */
class ServiceBuilder
{
    private const SINGLETON_KEY = '~singleton';

    protected AliasFinder $aliasFinder;

    protected ServiceResolver $serviceResolver;

    private array $sharedServices = [];

    private array $sharedNames = [];

    protected array $config;

    public function __construct(?AliasFinder $aliasFinder = null)
    {
        $this->aliasFinder = $aliasFinder ?? new AliasFinder();
        $this->serviceResolver = new ServiceResolver();
    }

    public function setConfig(array $config): static
    {
        $configResolved = $this->serviceResolver->resolveServices($config);

        $this->config = array_merge(
            $config,
            [
                'services' => $configResolved['services']
            ]
        );

        $this->sharedNames = $configResolved['sharedNames'];

        $this->sharedServices[self::SINGLETON_KEY] = [];

        return $this;
    }

    /**
     * @template TClass of object
     * @param class-string<TClass> $id
     * @return TClass
     *
     * @throws ReflectionException
     */
    public function createNewService(string $id): mixed
    {
        return $this->createService($id, true);
    }

    /**
     * @template TNew of object
     * @param class-string<TNew> $id
     * @return TNew
     *
     * @throws ReflectionException
     */
    public function createService(string $id, bool $isNew = false): mixed
    {
        if (!empty($this->sharedNames[$id])) {
            $id = $this->sharedNames[$id];
        }

        if (isset($this->sharedServices[$id]) && $isNew === false) {
            return $this->sharedServices[$id];
        }

        if (class_exists($id) && empty($this->config['services'][$id])) {
            $this->config['services'][$id] = [
                'class' => $id,
            ];
        } elseif (interface_exists($id)) {
            $ref = $this->aliasFinder->findAlias($id);

            if (empty($ref)) {
                throw new RuntimeException("Референс '{$id}' не найден.");
            }

            $this->config['services'][$id] = [
                'class' => $ref,
            ];
        }

        if (!isset($this->config['services'][$id])) {
            throw new RuntimeException("Сервис '{$id}' не найден в конфигурации.");
        }

        $serviceConfig = $this->config['services'][$id];

        if (isset($serviceConfig['value'])) {
            return $serviceConfig['value'];
        }

        if (!empty($serviceConfig['calls']) && is_array($serviceConfig['calls'])) {
            $serviceConfig = $serviceConfig['calls'];
        }

        if (isset($serviceConfig['class']) && $this->isSingleton($id, $serviceConfig)) {
            if (empty($this->sharedServices[self::SINGLETON_KEY][$id])) {
                $this->sharedServices[self::SINGLETON_KEY][$id] = $this->createInstance($serviceConfig);
            }

            return $this->getSingletonInstance($serviceConfig);
        }

        $instance = $this->createInstance($serviceConfig);

        $shared = $serviceConfig['shared'] ?? true;
        if ($shared && !$isNew) {
            if (!empty($serviceConfig['sharedName'])) {
                $id = $serviceConfig['sharedName'];
            }

            $this->sharedServices[$id] = $instance;
        }

        return $instance;
    }

    /**
     * @throws ReflectionException
     */
    protected function createInstance(array $serviceConfig): mixed
    {
        $class = $serviceConfig['class'] ?? null;
        if (!$class) {
            throw new RuntimeException("Не указан класс для создания сервиса.");
        }

        if ($this->hasAlias($serviceConfig['class'])) {
            $class = $this->aliasFinder->findAlias($serviceConfig['class']);
        }

        $arguments = $serviceConfig['createArguments'] ?? [];
        $autoDependencies = $serviceConfig['autoDependencies'] ?? true;

        if (!empty($serviceConfig['closure']) && is_callable($serviceConfig['closure'])) {
            $fn = new ReflectionFunction($serviceConfig['closure']);

            if ($autoDependencies) {
                $arguments = $this->resolveDependencies($fn, $arguments);
            }

            return $fn->invoke(...$arguments);
        }

        if ($autoDependencies) {
            $arguments = $this->resolveDependencies($class, $arguments);
        }

        $instance = new $class(...$arguments);

        // Вызов методов
        if (!empty($serviceConfig['methods'])) {
            foreach ($serviceConfig['methods'] as $method => $params) {
                if (method_exists($instance, $method)) {
                    $instance->$method(...$params);
                }
            }
        }

        return $instance;
    }

    private function resolveDependencies(string|ReflectionFunction $classOrFunction, array $explicitArguments): array
    {
        try {
            if ($classOrFunction instanceof ReflectionFunction) {
                $reflection = $classOrFunction;
                $parameters = $classOrFunction->getParameters();
            } else {
                $reflection = new ReflectionClass($classOrFunction);
                $parameters = $reflection->getConstructor()?->getParameters() ?? [];

                if (!$parameters) {
                    return $explicitArguments;
                }
            }

            $dependencies = [];
            foreach ($parameters as $parameter) {
                $name = $parameter->getName();

                if (array_key_exists($name, $explicitArguments)) {
                    if (!empty($explicitArguments[$name]['calls']) && is_array($explicitArguments[$name]['calls'])) {
                        $dependencies[] = $this->createInstance($explicitArguments[$name]['calls']);
                    } elseif (is_array($explicitArguments[$name]) && !empty($explicitArguments[$name]['class'])) {
                        $dependencies[] = $this->createInstance($explicitArguments[$name]);
                    } else {
                        $dependencies[] = $explicitArguments[$name];
                    }
                } elseif ($parameter->hasType() && !$parameter->getType()->isBuiltin()) {
                    $dependencies[] = $this->createService((string)$parameter->getType());
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new RuntimeException(
                        "Не удалось разрешить зависимость '{$name}' для '{$reflection->getName()}'."
                    );
                }
            }

            return $dependencies;
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    protected function getSingletonInstance(string $id): mixed
    {
        if (empty($this->sharedServices[self::SINGLETON_KEY][$id])) {
            throw new RuntimeException("singleton instance {$id} not found");
        }

        return $this->sharedServices[self::SINGLETON_KEY][$id]->getInstance();
    }

    private function hasAlias(string $id): bool
    {
        try {
            return !empty($this->aliasFinder->findAllAliases($id));
        } catch (RuntimeException $e) {
            return false;
        }
    }

    private function isSingleton(string $id, array $serviceConfig): bool
    {
        return str_starts_with($id, '~')
            && method_exists($serviceConfig['class'], 'getInstance');
    }

    public function hasService(string $id): bool
    {
        return isset($this->config['services'][$id]);
    }

    public function getAliasFinder(): AliasFinder
    {
        return $this->aliasFinder;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
    
    public function changeConfig(string $config): static
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function addService(string $id, array $config): bool
    {
        if (!empty($this->config['services'][$id])) {
            return false;
        }

        $this->config['services'][$id] = $this->serviceResolver->resolveServices(['services' => $config])['services'];

        return true;
    }

    public function registerAlias(string $reference, string $alias): void
    {
        $this->aliasFinder->registerAlias($reference, $alias);
    }
}
