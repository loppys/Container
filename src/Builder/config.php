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
    'name' => 'CMS',
    'group' => 'system',
    'handler' => \Vengine\Modules\CMS\Main::class,
    'package' => \Vengine\Modules\CMS\Info\Package::class,
  ],
  [
    'name' => 'migrations',
    'group' => 'system',
    'handler' => \Vengine\Modules\Migrations\Process::class,
    'package' => \Vengine\Modules\Migrations\Info\Package::class,
  ],
  [
    'name' => 'DataPageTransformer',
    'handler' => \Vengine\Controllers\Page\DataPageTransformer::class,
  ],
  [
    'name' => 'LocalPage',
    'handler' => \Vengine\Controllers\Page\LocalPage::class,
  ]
];
