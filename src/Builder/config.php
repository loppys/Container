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
  ]
];
