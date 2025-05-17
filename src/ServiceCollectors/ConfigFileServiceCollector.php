<?php

namespace Vengine\Libs\ServiceCollectors;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libs\Container;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\Settings\DefinitionSettings;

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
