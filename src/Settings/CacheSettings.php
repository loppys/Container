<?php

namespace Vengine\Libs\Settings;

class CacheSettings extends AbstractSettings
{
    protected string $name = 'cache';

    public function getCacheDriver(): ?string
    {
        if (empty($this->getOption('cache.driver'))) {
            return null;
        }

        if (
            empty($this->getOption('definition.map'))
            && empty($this->getOption('definition.objects'))
        ) {
            return null;
        }

        return $this->getOption('cache.driver');
    }
}
