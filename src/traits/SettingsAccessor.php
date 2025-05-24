<?php

namespace Vengine\Libs\DI\traits;

use Vengine\Libs\DI\interfaces\ContainerSettingsInterface;

/**
 * @template T2
 */
trait SettingsAccessor
{
    /**
     * @var ContainerSettingsInterface[]
     */
    protected array $settings = [];

    /**
     * @template TClass of object
     * @param class-string<TClass> $name
     * @return TClass
     */
    public function getSettingsByName(string $name): ?ContainerSettingsInterface
    {
        return $this->settings[$name] ?? null;
    }

    public function addSettings(ContainerSettingsInterface $settings): static
    {
        $this->settings[$settings->getName()] = $settings;
        $this->settings[$settings::class] = $settings;

        return $this;
    }

    /**
     * @return ContainerSettingsInterface[]
     */
    public function getAllSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param ContainerSettingsInterface[] $settings
     */
    public function setSettings(array $settings): static
    {
        foreach ($settings as $setting) {
            $this->addSettings($setting);
        }

        return $this;
    }
}
