<?php

namespace Vengine\Libs\Settings;

class PackageSettings extends AbstractSettings
{
    protected string $name = 'package';

    public function isEnabled(): bool
    {
        return $this->getOption('enabled', true);
    }

    public function getAbstractClass(): string
    {
        return $this->getOption('abstract.class', '');
    }
}
