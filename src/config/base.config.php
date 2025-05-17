<?php

use Vengine\Libs\interfaces\SingletonInterface;
use Vengine\Libs\Storage\ContainerSignStorage;
use Vengine\Libs\interfaces\ReplaceDefinitionInterface;
use Vengine\Libs\Settings\Storage\SettingsStorage;

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
