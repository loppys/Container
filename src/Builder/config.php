<?php

return [
  [
    'name' => '_startup',
    'handler' => \Vengine\Startup::class,
    'group' => 'system',
    'package' => false,
    'create' => true,
  ],
  [
    'name' => 'LocalPage',
    'handler' => \Vengine\Controllers\Page\LocalPage::class,
  ]
];
