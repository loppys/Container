<?php

namespace Vengine\Libs\DI\config;

use Vengine\Libs\DI\Exceptions\ConfigResolveException;
use Vengine\Libs\DI\interfaces\ConfigureInterface;
use Vengine\Libs\DI\interfaces\ContainerSettingsInterface;

class ConfigResolver
{
    /**
     * @throws ConfigResolveException
     */
    public function configResolve(ConfigureInterface $configure): array
    {
        $baseConfig = require $configure->getConfigPath();
        $baseConfig['services'] = require $baseConfig['services'];

        if ($configure instanceof OverwriteConfigure) {
            $overwriteConfig = require $configure->getOverwriteConfigPath();

            if (!empty($overwriteConfig['services'])) {
                if (is_string($overwriteConfig['services']) && file_exists($overwriteConfig['services'])) {
                    $overwriteConfig['services'] = require $overwriteConfig['services'];
                }

                if (!is_array($overwriteConfig['services'])) {
                    throw new ConfigResolveException('option `services` is not array.');
                }

                $this->replaceResolve($baseConfig['services'], $overwriteConfig['services']);

                unset($overwriteConfig['services']);
            }

            if (!empty($overwriteConfig['settings'])) {
                if (!is_array($overwriteConfig['settings'])) {
                    throw new ConfigResolveException('option `settings` is not array.');
                }

                $this->replaceResolve($baseConfig['settings'], $overwriteConfig['settings']);

                unset($overwriteConfig['settings']);
            }

            $baseConfig = $this->replaceResolve($baseConfig, $overwriteConfig);
        }

        foreach ($baseConfig['settings'] as $sk => $sv) {
            if (!class_exists($sk)) {
                continue;
            }

            if (!in_array(ContainerSettingsInterface::class, (array)class_implements($sk), true)) {
                continue;
            }

            /** @var ContainerSettingsInterface $settingObject */
            $settingObject = new $sk($sv);

            $baseConfig['settings'][$settingObject->getName()] = $settingObject;

            unset($baseConfig['settings'][$sk]);
        }

        return $baseConfig;
    }

    private function replaceResolve(array $baseArr, array $replaced): array
    {
        foreach ($baseArr as $key => $value) {
            if (isset($replaced[$key]) && is_array($replaced[$key]) && is_array($value)) {
                $baseArr[$key] = $this->replaceResolve($value, $replaced[$key]);
            } elseif (isset($replaced[$key])) {
                $baseArr[$key] = $replaced[$key];
            }
        }

        return $baseArr;
    }
}
