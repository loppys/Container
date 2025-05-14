<?php

namespace Vengine\Libs\ServiceCollectors;

use Closure;
use Vengine\Libs\Container;
use Vengine\Libs\Exceptions\ContainerException;
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

    //    'profiling.timer' => [
    //        'sharedTags' => ['timer'],
    //        'refs' => [
    //            TimerInterface::class,
    //        ],
    //        'closure' => function (array $logs = []) {
    //            return new Timer($logs);
    //        },
    //    ],
    //    'reference.storage' => [
    //        'class' => ReferenceStorage::class,
    //        'shared' => false,
    //    ],
    //    'reference.finder' => [
    //        'class' => ReferenceFinder::class,
    //        'arguments' => [
    //            'storage' => '@reference.storage',
    //        ],
    //    ],
    //    'cache.configurator' => [
    //        'class' => Configurator::class,
    //        'arguments' => [
    //            'defaultCacheDriver' => DriverStorage::CONFIG_DRIVER,
    //        ],
    //        'shared' => false,
    //    ],
    //    'definition.cache.driver' => [
    //        'sharedTags' => [
    //            'DefinitionCache'
    //        ],
    //        'closure' => function (Configurator $configurator, array $options) {
    //            return (new CacheManager($configurator))->createDriver('config', $options);
    //        },
    //        'arguments' => [
    //            'configurator' => '@cache.configurator',
    //            'options' => [],
    //        ],
    //    ],
    /**
     * @throws ContainerException
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
