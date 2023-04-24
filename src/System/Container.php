<?php

namespace Loader\System;

use Loader\Libraries\Alias\AliasAdapter;
use Loader\Libraries\Alias\DTO\Alias;
use Loader\Libraries\Alias\Interfaces\AliasAdapterInterface;
use Loader\Libraries\Alias\Interfaces\AliasInterface;
use Loader\Libraries\Alias\Storages\AliasStorage;
use Loader\System\Exceptions\ClassNotFoundException;
use Loader\System\Exceptions\ContainerException;
use Loader\System\Helpers\Reflection;
use Loader\System\Interfaces\BuilderInterface;
use Loader\System\Interfaces\ContainerFastCall;
use Loader\System\Interfaces\ContainerInterface;
use Loader\System\Interfaces\ContainerInjection;
use Loader\System\Interfaces\PackageInterface;
use Loader\System\Interfaces\SingletonInterface;
use Loader\System\Interfaces\StorageInterface;
use Loader\System\Storage\CommonObjectStorage;
use Loader\System\Storage\DataStorage;
use Loader\System\DTO\Package;
use ReflectionMethod;
use ReflectionException;

class Container implements ContainerInterface, ContainerFastCall, AliasAdapterInterface, SingletonInterface
{
    /**
     * @var Container
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $sharedList;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var CommonObjectStorage
     */
    private $objectStorage;

    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AliasAdapterInterface
     */
    private $aliasAdapter;

    public function __construct()
    {
        $this->storage = new DataStorage();
        $this->objectStorage = new CommonObjectStorage();
        $this->builder = new Builder();
        $this->config = new Config();

        static::$instance = $this;
        
        $this->aliasAdapter = $this->createObject(AliasAdapter::class);

        static::$instance = $this;
    }

    /**
     * @inheritDoc
     * 
     * @throws ReflectionException
     */
    public function get(string $name, array $arguments = [])
    {
        if (empty($arguments)) {
            return $this->createObject($name, $arguments);
        }
        
        return $this->getShared($name);
    }

    /**
     * @return ContainerInterface|Container
     */
    public static function getInstance(): Container
    {
        if (static::$instance) {
            return static::$instance;
        }

        throw new ContainerException('container not init');
    }

    /**
     * @inheritDoc
     */
    public function getShared(string $name)
    {
        if (!empty($this->sharedList[$name])) {
            return $this->sharedList[$name];
        }

        if (Reflection::classExist($name)) {
            $this->setShared($name, $this->createObject($name));

            return $this->getShared($name);
        }

        throw new ContainerException("[ {$name} ] not init or not found");
    }

    /**
     * @inheritDoc
     */
    public function setShared(string $name, $value): ContainerInterface
    {
        $this->sharedList[$name] = $value;

        return $this;
    }

    /**
     * @param PackageInterface $package
     * @return ContainerInjection
     *
     * @throws ReflectionException
     */
    public function build(PackageInterface $package): ContainerInjection
    {
        $className = $package->getClassName();

        if (!Reflection::classExist($className)) {
            throw new ClassNotFoundException($className);
        }

        return $this->builder->build($package);
    }

    public function getComponent(string $name, string $method = '', array $methodArguments = []): ContainerInjection
    {
        $package = $this->getPackage($name);

        if (!$package->hasObject()) {
            throw new ContainerException("Component {$name} not created");
        }

        $component = $package->getObject();

        if ($component instanceof ContainerInjection) {
            if (!empty($method)) {
                $this->builder->invoke($component, $method, $methodArguments);
            }

            return $component;
        }

        throw new ContainerException('object not implements ContainerInjection interface');
    }

    /**
     * @param string $name
     * @return ContainerInjection
     *
     * @throws ReflectionException
     */
    public function getNew(string $name): ContainerInjection
    {
        return $this->builder->getNew($name);
    }

    /**
     * @param string $class
     * @param array $arguments
     *
     * @return object
     *
     * @throws ReflectionException
     */
    public function createObject(string $class, array $arguments = [])
    {
        return $this->builder->createObject($class, $arguments);
    }

    /**
     * @param string $class
     * @return ReflectionMethod|null
     *
     * @throws ReflectionException
     */
    public function getConstructor(string $class): ?ReflectionMethod
    {
        return Reflection::getConstructor($class);
    }

    public function newPackage(string $name): PackageInterface
    {
        return new Package($name);
    }

    /**
     * @param string $class
     * @param string $method
     * @return array
     *
     * @throws ReflectionException
     */
    public function getArguments(string $class, string $method): array
    {
        return Reflection::getArguments($class, $method);
    }

    public function addPackage(PackageInterface $package): ContainerInterface
    {
        $name = $package->getName();

        if (empty($name)) {
            return $this;
        }

        if (!$this->storage->has($name)) {
            $this->storage->add($name, $package);
        }

        return $this;
    }

    public function getPackage(string $name): PackageInterface
    {
        if ($this->storage->has($name)) {
            return $this->storage->get($name);
        }

        $package = $this->newPackage($name);

        $this->storage->set($name, $package);

        return $package;
    }

    /*
     * name => string
     * className => string
     * arguments => array
     * defaultMethod => string
     * defaultMethodArguments => array
     */
    public function packageCollect(array $packageList): bool
    {
        if (empty($packageList)) {
            return false;
        }

        foreach ($packageList as $key => $package) {
            if ($key === 'alias') {
                if (is_array($package)) {
                    foreach ($package as $class => $aliasInfo) {
                        if (empty($class) || empty($aliasInfo)) {
                            continue;
                        }

                        $ownerAlias = is_array($aliasInfo) ? $aliasInfo[0] : $aliasInfo;

                        $this->setNewAlias($ownerAlias, $class);

                        if (is_array($aliasInfo)) {
                            $alias = $this->getAlias($ownerAlias);

                            $other = [];

                            foreach ($aliasInfo as $item) {
                                if (empty($item)) {
                                    continue;
                                }

                                $alias->add($item);
                            }
                        }
                    }
                }

                continue;
            }

            if (empty($package['name']) || !is_array($package)) {
                continue;
            }

            $tempPackage = $this->getPackage($package['name']);

            if (is_string($package['className'])) {
                $tempPackage->setClassName($package['className']);
            }

            if (is_array($package['arguments'])) {
                $tempPackage->setArguments($package['arguments']);
            }

            if (is_string($package['defaultMethod'])) {
                $tempPackage->setDefaultMethod($package['defaultMethod']);
            }

            if (is_array($package['defaultMethodArguments'])) {
                $tempPackage->setDefaultMethodArguments($package['defaultMethodArguments']);
            }
        }

        return true;
    }

    public function setStorage(StorageInterface $storage): ContainerInterface
    {
        $this->storage = $storage;

        return $this;
    }

    public function setBuilder(BuilderInterface $builder): ContainerInterface
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @return StorageInterface|DataStorage
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @return BuilderInterface|Builder
     */
    public function getBuilder(): BuilderInterface
    {
        return $this->builder;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getObjectStorage(): CommonObjectStorage
    {
        return $this->objectStorage;
    }

    public function addAlias(string $name, string $alias): AliasAdapterInterface
    {
        return $this->aliasAdapter->addAlias($name, $alias);
    }

    public function removeAlias(string $name): AliasAdapterInterface
    {
        return $this->aliasAdapter->removeAlias($name);
    }

    public function setAlias(AliasInterface $alias): AliasAdapterInterface
    {
        return $this->aliasAdapter->setAlias($alias);
    }

    public function getAlias(string $name): Alias
    {
        return $this->aliasAdapter->getAlias($name);
    }

    public function setClassNameForAlias(string $name, string $class): AliasInterface
    {
        return $this->aliasAdapter->setClassNameForAlias($name, $class);
    }

    public function hasAliasByName(string $name, string $alias): bool
    {
        return $this->aliasAdapter->hasAliasByName($name, $alias);
    }

    public function hasAliasInStorage(string $name): bool
    {
        return $this->aliasAdapter->hasAliasInStorage($name);
    }

    public function getAliasStorage(): AliasStorage
    {
        return $this->aliasAdapter->getAliasStorage();
    }

    public function setNewAlias(string $name, string $class): void
    {
        $alias = Alias::getNew($name)->setClassName($class);

        $this->aliasAdapter->setAlias($alias);
    }
}
