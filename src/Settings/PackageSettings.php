<?php

namespace Vengine\Libs\DI\Settings;

class PackageSettings extends AbstractSettings
{
    protected string $name = 'package';

    public function isEnabled(): bool
    {
        return $this->getOption('enabled', true);
    }
}
