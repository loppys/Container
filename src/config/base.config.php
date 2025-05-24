<?php

use Vengine\Libs\DI\interfaces\SingletonInterface;
use Vengine\Libs\DI\Storage\ContainerSignStorage;
use Vengine\Libs\DI\interfaces\ReplaceDefinitionInterface;
use Vengine\Libs\DI\Settings\Storage\SettingsStorage;

return [
    'services' => __DIR__ . '/services.config.php',
    'settings' => [
        SettingsStorage::DEFINITION => [
            'auto.shared' => true,
            'enable.overwrite' => true,
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
            'logPath' => ($_SERVER['DOCUMENT_ROOT'] ?: __DIR__) . sprintf('/profiling-%s.log', time()),
        ],
        SettingsStorage::PACKAGES => [
            'enabled' => true,
        ],
    ],
];
