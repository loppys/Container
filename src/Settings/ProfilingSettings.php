<?php

namespace Vengine\Libs\Settings;

class ProfilingSettings extends AbstractSettings
{
    protected string $name = 'profiling';

    public function isEnabled(): bool
    {
        return $this->getOption('enabled', false);
    }

    public function getTimerConfig(): ?array
    {
        $timerService = $this->getOption('timer.service');
        if (empty($timerService)) {
            return null;
        }

        return [
            $timerService,
            [
                'logPath' => $this->getOption('logPath', '')
            ]
        ];
    }
}
