<?php

namespace Vengine\Libs\DI\config;

use Vengine\Libs\DI\interfaces\ConfigureInterface;

class Configure implements ConfigureInterface
{
    public function __construct(
        protected readonly string $configPath = __DIR__ . '/base.config.php'
    ) {
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }
}
