<?php

namespace Vengine\Libs\Settings;

class DefinitionSettings extends AbstractSettings
{
    protected string $name = 'definition';

    public function isAutoShared(): bool
    {
        return $this->getOption('auto.shared', true);
    }

    public function isEnabledOverwrite(): bool
    {
        return $this->getOption('enable.overwrite', true);
    }
}
