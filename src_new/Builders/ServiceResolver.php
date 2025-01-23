<?php

namespace Vengine\Libs\Builders;

class ServiceResolver
{
    private array $config = [];

    public function resolveServices(array $config = []): array
    {
        $this->config = $config;
        $processedServices = [];
        $sharedNames = [];

        foreach ($config['services'] as $name => $serviceConfig) {
            if (!is_array($serviceConfig)) {
                continue;
            }

            if (!empty($serviceConfig['sharedName'])) {
                $sharedNames[$serviceConfig['sharedName']] = $name;
            }

            if (str_starts_with($name, '@')) {
                continue;
            }

            $processedServices[$name] = $this->resolveService($serviceConfig);
        }

        $this->config = [];

        return [
            'sharedNames' => $sharedNames,
            'services' => $processedServices
        ];
    }

    private function resolveService(array $serviceConfig): array
    {
        foreach ($serviceConfig as $key => $value) {
            if (is_string($value)) {
                $serviceConfig[$key] = $this->resolveReference($value);
            } elseif (is_array($value)) {
                $serviceConfig[$key] = $this->resolveService($value);
            }
        }

        return $serviceConfig;
    }

    private function resolveReference(string $reference)
    {
        if (str_starts_with($reference, '@')) {
            $serviceName = $reference;
            if (!isset($this->config['services'][$serviceName])) {
                throw new \RuntimeException("Сервис '{$serviceName}' не найден.");
            }

            return $this->resolveService($this->config['services'][$serviceName]);
        } elseif (str_starts_with($reference, '#')) {
            $pathName = $reference;
            return $this->config['paths'][$pathName] ?? throw new \RuntimeException("Путь '{$pathName}' не найден.");
        }

        return $reference;
    }
}
