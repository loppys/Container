<?php

use Vengine\Libs\interfaces\SingletonInterface;
use Vengine\Libs\Storage\ContainerSignStorage;
use Vengine\Libs\interfaces\ReplaceDefinitionInterface;
use Vengine\Libs\Settings\Storage\SettingsStorage;
use Vengine\Libs\Packages\AbstractPackage;

return [
    'services' => __DIR__ . '/services.config.php',
    'settings' => [
        SettingsStorage::CACHE => [
            'cache.driver' => '@definition.cache.driver',
            'definition.map' => true,
            'definition.objects' => false, // use only for debugging
        ],
        SettingsStorage::DEFINITION => [
            'auto.shared' => true,
            'enable.overwrite' => true,
            'references' => true,
            'replaced' => [
                'interface' => ReplaceDefinitionInterface::class,
            ],
            'singleton' => [
                'interface' => SingletonInterface::class,
                'sing' => ContainerSignStorage::SINGLETON,
            ],
            'link.service' => [
                'sing' => ContainerSignStorage::LINK_OTHER_SERVICE,
            ],
        ],
        SettingsStorage::PROFILING => [
            'enabled' => false,
            'timer.service' => '@profiling.timer',
            'logs' => [
                'default.output' => 'file',
                'output' => [
                    'file' => [
                        'depth' => -1, // unlimited
                        'path' => ($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) . sprintf('/profiling-%s.log', time())
                    ],
                    'print' => [
                        'depth' => 10,
                    ],
                ],
            ],
        ],
        SettingsStorage::PACKAGES => [
            'enabled' => true,
            'abstract.class' => AbstractPackage::class,
        ],
    ],
];
