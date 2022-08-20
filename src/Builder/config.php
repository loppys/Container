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
    'name' => 'DataPageTransformer',
    'handler' => \Vengine\Controllers\Page\DataPageTransformer::class,
  ],
  [
    'name' => 'LocalPage',
    'handler' => \Vengine\Controllers\Page\LocalPage::class,
    'call' => 'DataPageTransformer',
  ]
];
