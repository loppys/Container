<?php

namespace Loader\System;

use Loader\System\Exceptions\ClassNotFoundException;
use Loader\System\Exceptions\ContainerException;
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
            $temp = $this->getContainer()->getPackage($package['name']);

            if (!is_array($package)) {
                continue;
            }

            if (is_string($package['className'])) {
                $temp->setClassName($package['className']);
            }

            if (is_array($package['arguments'])) {
                $temp->setArguments($package['arguments']);
            }

            if (is_string($package['defaultMethod'])) {
                $temp->setDefaultMethod($package['defaultMethod']);
            }

            if (is_array($package['defaultMethodArguments'])) {
                $temp->setDefaultMethodArguments($package['defaultMethodArguments']);
            }
        }

        return true;
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

        if ($package->hasObject()) {
            return $package->getObject();
        }

        $constructor = Reflection::getConstructor($className);

        $method = '';
        if ($constructor) {
            $method = $constructor->getName();
        }

        $arguments = $package->getArguments() ?: $this->defineArguments($className, $method);

        if (count($arguments) > 0) {
            foreach ($arguments as $key => $argument) {
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

                    $arguments[$key] = $this->createObject($class, $this->defineArguments($class, $method));

                    continue;
                }

                $argumentPackage = $this->getContainer()->getPackage($name);
                $argumentPackage->setClassName($class);

                $arguments[$key] = $this->build($argumentPackage);
            }
        }

        $package->setArguments($arguments);

        if ($package->hasObject()) {
            $object = $package->getObject();
        } else {
            $object = $this->createObject(
                $package->getClassName(),
                $arguments
            );
        }

        if (!$object || !is_object($object)) {
            throw new ContainerException('object not created or is not object');
        }

        if ($object instanceof ContainerInjection) {
            $package->setObject($object);

            $defaultMethod = $package->getDefaultMethod();

            if ($defaultMethod) {
                $package->setDefaultMethod($defaultMethod);

                $this->invoke($object, $defaultMethod, $package->getDefaultMethodArguments());
            }

            return $object;
        }

        throw new ContainerException('object not implements ContainerInjection interface');
    }

    /**
     * @param string $class
     * @param array $arguments
     *
     * @return object|null
     */
    public function createObject(string $class, array $arguments = [])
    {
        $object = null;

        if (!Reflection::classExist($class)) {
            throw new ClassNotFoundException($class);
        }

        if (!empty($arguments)) {
            $object = new $class(...$arguments);
        } else {
            $object = new $class;
        }

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
