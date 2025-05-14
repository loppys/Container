<?php

use Vengine\Libs\Profiling\Timer;
use Vengine\Libs\Profiling\TimerInterface;
use Vengine\Libs\References\ReferenceFinder;
use Vengine\Libs\References\ReferenceStorage;
use Vengine\Cache\CacheManager;
use Vengine\Cache\config\Configurator;
use Vengine\Cache\Storage\DriverStorage;

//По @profiling.timer
//'default.output' => 'file',
//                'output' => [
//    'file' => [
//        'depth' => -1, // unlimited
//        'path' => ($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) . sprintf('/profiling-%s.log', time())
//    ],
//    'print' => [
//        'depth' => 10,
//    ],
//],

return [
    'profiling.timer' => [
        'sharedTags' => ['timer'],
        'refs' => [
            TimerInterface::class,
        ],
        'closure' => function (array $logs = []) {
            return new Timer($logs);
        },
    ],
    'reference.storage' => [
        'class' => ReferenceStorage::class,
        'shared' => false,
    ],
    'reference.finder' => [
        'class' => ReferenceFinder::class,
        'arguments' => [
            'storage' => '@reference.storage',
        ],
    ],
    'cache.configurator' => [
        'class' => Configurator::class,
        'arguments' => [
            'defaultCacheDriver' => DriverStorage::CONFIG_DRIVER,
        ],
        'shared' => false,
    ],
    'definition.cache.driver' => [
        'sharedTags' => [
            'DefinitionCache'
        ],
        'closure' => function (Configurator $configurator, array $options) {
            return (new CacheManager($configurator))->createDriver('config', $options);
        },
        'arguments' => [
            'configurator' => '@cache.configurator',
            'options' => [],
        ],
    ],
];
