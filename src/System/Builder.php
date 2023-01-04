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
use ReflectionException;

class Builder implements BuilderInterface
{
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

        foreach ($packageList as $package) {
            if (empty($package['name']) || !is_array($package)) {
                continue;
            }

            $tempPackage = $this->getContainer()->getPackage($package['name']);

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

        if (count($argumentList) > 0) {
            //create arguments
            foreach ($argumentList as $key => $argument) {
                if (empty($argument->getType())) {
                    continue;
                }

                $name = Reflection::getClassShortName($argument->getType()->getName());
                $class = $argument->getType()->getName();

                if (!Reflection::classExist($class)) {
                    continue;
                }

                if (!$this->getConfig()->createArgumentPackage) {
                    $constructor = Reflection::getConstructor($class);

                    $method = '';
                    if ($constructor) {
                        $method = $constructor->getName();
                    }

                    $argumentList[$key] = $this->createObject($class, $this->defineArguments($class, $method));

                    continue;
                }

                $argumentPackage = $this->getContainer()->getPackage($name);
                $argumentPackage->setClassName($class);

                $argumentList[$key] = $this->build($argumentPackage);
            }
        }

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
     */
    public function createObject(string $class, array $arguments = [])
    {
        $objectStorage = $this->getContainer()->getObjectStorage();

        if ($objectStorage->has($class)) {
            return $objectStorage->getObject($class);
        }

        if (!Reflection::classExist($class)) {
            throw new ClassNotFoundException($class);
        }

        if (!empty($arguments)) {
            $object = new $class(...$arguments);
        } else {
            $object = new $class;
        }

        $objectStorage->add(
            strtolower(Reflection::getClassShortName($class)),
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
