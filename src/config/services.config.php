<?php

use Vengine\Libs\Profiling\Timer;

return [
    'profiling.timer' => [
        'sharedTags' => ['timer'],
        'closure' => function (string $logPath = '') {
            return new Timer($logPath);
        },
    ],
];
