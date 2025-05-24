<?php

namespace Vengine\Libs\DI\Settings\Storage;

use Vengine\Libs\DI\Settings\DefinitionSettings;
use Vengine\Libs\DI\Settings\PackageSettings;
use Vengine\Libs\DI\Settings\ProfilingSettings;

class SettingsStorage
{
    public const DEFINITION = DefinitionSettings::class;
    public const PROFILING = ProfilingSettings::class;
    public const PACKAGES = PackageSettings::class;
}
