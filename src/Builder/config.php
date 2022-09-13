<?php

return [
  [
    'name' => 'startup',
    'handler' => \Vengine\Startup::class,
    'group' => \Loader\Builder\Storage::GROUP_SYSTEM
  ],
  [
    'name' => 'LocalPage',
    'handler' => \Vengine\Controllers\Page\LocalPage::class,
  ],
  [
    'name' => 'Adapter',
    'handler' => \Vengine\Database\Adapter::class,
    'group' => \Loader\Builder\Storage::GROUP_SYSTEM
  ]
];
