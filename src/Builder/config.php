<?php

use Vengine\Startup;
use Vengine\System\Components\Database\Adapter;
use Loader\Builder\Storage;
use Vengine\Controllers\Page\LocalPage;

return [
  [
    'name' => 'startup',
    'handler' => [Startup::class, 'init'],
    'group' => Storage::GROUP_SYSTEM
  ],
  [
    'name' => 'LocalPage',
    'handler' => LocalPage::class,
  ],
  [
    'name' => 'Adapter',
    'handler' => [Adapter::class, 'connect'],
    'group' => Storage::GROUP_SYSTEM
  ]
];
