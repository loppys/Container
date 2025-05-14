<?php

namespace Vengine\Libs\interfaces;

interface ContainerSettingsInterface
{
    public function getName(): string;
    public function getOption(string|array $key, mixed $default = null): mixed;
    public function getOptions(): array;
    public function setOptions(array $options): void;
}
