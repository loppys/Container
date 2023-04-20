<?php

namespace Loader\System;

use Loader\System\Exceptions\ClassNotFoundException;
use Loader\System\Exceptions\ContainerException;
use Loader\System\Helpers\EmptyClass;
use Loader\System\Helpers\Reflection;
use Loader\System\Interfaces\BuilderInterface;
use Loader\System\Interfaces\ContainerInterface;
use Loader\System\Interfaces\ContainerInjection;
use Loader\System\Interfaces\PackageInterface;
use ReflectionParameter;
use ReflectionException;

class Builder implements BuilderInterface
{
    /**
     * @param PackageInterface $package
     * @param bool $new
     * @return ContainerInjection
     *
     * @throws ReflectionException
     */
    public function build(PackageInterface $package, bool $new = false): ContainerInjection
    {
        if (!$new && $package->hasObject()) {
            return $package->getObject();
        }

        $className = $package->getClassName();

        if (!Reflection::classExist($className)) {
            throw new ClassNotFoundException($className);
        }

        $constructor = Reflection::getConstructor($className);

        $method = '';
        if ($constructor) {
            $method = $constructor->getName();
        }

        $argumentList = $package->getArguments() ?: $this->defineArguments($className, $method);

        $this->createArgument($argumentList);

        $package->setArguments($argumentList);

        $object = $this->createObject(
            $package->getClassName(),
            $argumentList
        );

        if (!$object || !is_object($object)) {
            throw new ContainerException('object not created or is not object');
        }

        if ($object instanceof ContainerInjection) {
            $package->setObject($object);

            $defaultMethod = $package->getDefaultMethod();

            if ($defaultMethod) {
                $this->invoke($object, $defaultMethod, $package->getDefaultMethodArguments());
            }

            return $object;
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
        $container = $this->getContainer();
        $storage = $container->getStorage();
        $objectStorage = $container->getObjectStorage();

        if ($storage->has($name)) {
            $package = $container->getPackage($name);

            $class = $package->getClassName();

            if ($objectStorage->has($class)) {
                $objectStorage->delete($class);
            }

            $package->update([
                'object' => new EmptyClass()
            ]);

            return $this->build($package, true);
        }

        throw new ContainerException("Failed new to create {$name}");
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
        $lowerName = strtolower($class);
        $info = Reflection::get($class);

        if ($info !== null) {
            $class = $info->getName();
        }

        $objectStorage = $this->getContainer()->getObjectStorage();

        if ($objectStorage->has($lowerName ?: $class)) {
            return $objectStorage->getObject($lowerName ?: $class);
        }

        if (!Reflection::classExist($class)) {
            throw new ClassNotFoundException($class);
        }

        if (empty($arguments)) {
            $arguments = $this->defineArguments($class, '__construct');
        }

        $this->createArgument($arguments);

        $arguments = array_filter($arguments, static function($item) {
            if ($item instanceof ReflectionParameter) {
                return false;
            }

            return true;
        });

        if (!empty($arguments)) {
            $object = new $class(...$arguments);
        } else {
            $object = new $class;
        }

        $objectStorage->add(
            $lowerName,
            $object
        );

        return $object;
    }

    /**
     * @param string $class
     * @param string $method
     * @return array
     *
     * @throws ReflectionException
     */
    public function defineArguments(string $class, string $method = ''): array
    {
        $arguments = [];

        if (!Reflection::classExist($class)) {
            throw new ClassNotFoundException($class);
        }

        if (method_exists($class, $method)) {
            $arguments = Reflection::getArguments($class, $method);
        }

        return $arguments;
    }

    /**
     * @param array $arguments
     *
     * @throws ReflectionException
     */
    protected function createArgument(array &$arguments): void
    {
        if (count($arguments) > 0) {
            foreach ($arguments as $key => $argument) {
                if (
                    (is_object($argument) && !method_exists($argument, 'getType'))
                    || (!is_object($argument) &&
                        (is_string($argument) || is_array($argument) || is_int($argument) || is_bool($argument))
                    )
                ) {
                    $arguments[$key] = $argument;

                    continue;
                }

                if (empty($argument->getType())) {
                    continue;
                }

                $name = Reflection::getClassShortName($argument->getType()->getName());
                $class = $argument->getType()->getName();

                if (empty($class)) {
                    continue;
                }

                if (!Reflection::classExist($class)) {
                    continue;
                }

                if (!$this->getConfig()->createArgumentPackage) {
                    $constructor = Reflection::getConstructor($class);

                    $method = '';
                    if ($constructor) {
                        $method = $constructor->getName();
                    }

                    $arguments[$key] = $this->createObject($class, $this->defineArguments($class, $method));

                    continue;
                }

                $argumentPackage = $this->getContainer()->getPackage($name);
                $argumentPackage->setClassName($class);

                $arguments[$key] = $this->build($argumentPackage);
            }
        }
    }

    /**
     * @param object $object
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function invoke(object $object, string $method, array $arguments = [])
    {
        if (is_object($object) && method_exists($object, $method)) {
            return call_user_func_array([$object, $method], $arguments);
        }

        return false;
    }

    public function getContainer(): ContainerInterface
    {
        return Container::getInstance();
    }

    public function getConfig(): Config
    {
        return $this->getContainer()->getConfig();
    }
}
