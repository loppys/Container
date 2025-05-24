<?php

namespace Vengine\Libs\DI\ServiceCollectors;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libs\DI\Container;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use Vengine\Libs\DI\Settings\DefinitionSettings;

class ConfigFileServiceCollector extends AbstractServiceCollector
{
    /**
     * @throws ContainerException
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new ContainerException("Service config file not found: {$filePath}");
        }

        $this->definitions = include $filePath;
    }

    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    protected function delegateCollect(Container $container): void
    {
        $autoShared = $container->getSettingsByName(DefinitionSettings::class)->isAutoShared();

        foreach ($this->definitions as $id => $definition) {
            $definition['shared'] ??= $autoShared;

            $container->addRawService($id, $definition);
        }
    }
}
