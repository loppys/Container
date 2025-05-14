<?php

namespace Vengine\Libs\config;

use Vengine\Libs\interfaces\ConfigureInterface;

class Configure implements ConfigureInterface
{
    public function __construct(
        protected readonly string $configPath = __DIR__ . '/base.config.php'
    ) {}

    public function getConfigPath(): string
    {
        return $this->configPath;
    }
}
