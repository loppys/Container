<?php

use Vengine\Libs\DI\Profiling\Timer;

return [
    'profiling.timer' => [
        'sharedTags' => ['timer'],
        'closure' => function (string $logPath = '') {
            return new Timer($logPath);
        },
    ],
];
