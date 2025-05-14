<?php

namespace Vengine\Libs\Settings\Storage;

use Vengine\Libs\Settings\CacheSettings;
use Vengine\Libs\Settings\DefinitionSettings;
use Vengine\Libs\Settings\PackageSettings;
use Vengine\Libs\Settings\ProfilingSettings;

class SettingsStorage
{
    public const CACHE = CacheSettings::class;
    public const DEFINITION = DefinitionSettings::class;
    public const PROFILING = ProfilingSettings::class;
    public const PACKAGES = PackageSettings::class;
}
