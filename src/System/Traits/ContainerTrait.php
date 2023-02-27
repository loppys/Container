<?php

namespace Loader\System\Traits;

use Loader\System\Container;
use Loader\System\Helpers\Reflection;
use Loader\System\Interfaces\ContainerInterface;

trait ContainerTrait
{
    /**
     * @var ContainerInterface|Container
     */
    protected $container;

    public function getContainer(): ContainerInterface
    {
        return $this->container ?: Container::getInstance();
    }

    public static function getClassName(): string
    {
        return static::class;
    }

    public static function getName(): string
    {
        return Reflection::getClassShortName(static::class);
    }
}
