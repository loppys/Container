<?php

namespace Vengine\Libs\Settings;

use Vengine\Libs\interfaces\ContainerSettingsInterface;

abstract class AbstractSettings implements ContainerSettingsInterface
{
    protected string $name = '';

    private array $options = [];

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOption(string|array $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            $current = null;

            foreach ($key as $ko) {
                if (is_array($current) && array_key_exists($ko, $current)) {
                    $current = $current[$ko];
                } else {
                    return $default ?? null;
                }
            }

            return $current;
        }

        return $this->options[$key] ?? $default ?? null;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
