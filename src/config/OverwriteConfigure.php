<?php

namespace Vengine\Libs\DI\config;

class OverwriteConfigure extends Configure
{
    protected string $overwriteConfigPath = '';

    public function setOverwriteConfigPath(string $overwriteConfigPath): void
    {
        $this->overwriteConfigPath = $overwriteConfigPath;
    }

    public function getOverwriteConfigPath(): string
    {
        return $this->overwriteConfigPath;
    }
}
