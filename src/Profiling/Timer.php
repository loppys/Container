<?php

namespace Vengine\Libs\Profiling;

class Timer implements TimerInterface
{
    /**
     * @var TimerPoint[]
     */
    private array $stack = [];

    /**
     * @var TimerPoint[]
     */
    private array $points = [];

    private array $log = [];

    private bool $logToFile = false;

    private string $logFilePath = __DIR__ . '/timer.log';

    public function __construct(string $path = '')
    {
        if (!empty($path)) {
            $this->logToFile = true;
            $this->logFilePath = $path;
        }
    }

    public function setLogToFile(bool $enable): static
    {
        $this->logToFile = $enable;

        return $this;
    }

    public function setLogFilePath(string $path): static
    {
        $this->logFilePath = $path;
        return $this;
    }

    public function addPoint(string $point, mixed $data = null): static
    {
        $time = microtime(true);
        $depth = count($this->stack);

        $entry = [
            'action' => 'addPoint',
            'point' => $point,
            'data' => $data,
            'time' => $time,
            'depth' => $depth,
        ];

        $this->log[] = $entry;

        if ($this->logToFile) {
            $this->logToFile($entry);
        }

        $newPoint = new TimerPoint($point, $data);

        if (empty($this->stack)) {
            $this->points[] = $newPoint;
        } else {
            $parent = end($this->stack);
            $parent->children[] = $newPoint;
        }

        $this->stack[] = $newPoint;

        return $this;
    }

    public function endPoint(string $point): static
    {
        $time = microtime(true);
        $depth = count($this->stack);

        $entry = [
            'action' => 'endPoint',
            'point' => $point,
            'time' => $time,
            'depth' => $depth,
        ];
        $this->log[] = $entry;

        if ($this->logToFile) {
            $this->logToFile($entry);
        }

        while (!empty($this->stack)) {
            $current = array_pop($this->stack);
            $current->end();

            if ($current->name === $point) {
                break;
            }
        }

        if (!$this->logToFile) {
            $this->printLog();
        }

        return $this;
    }

    public function getPoints(): array
    {
        return $this->points;
    }

    public function getLog(): array
    {
        return $this->log;
    }

    public function printPoints(array $points = null, int $level = 0): void
    {
        $points ??= $this->points;

        foreach ($points as $point) {
            $duration = number_format($point->duration(), 4);
            $data = json_encode($point->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            echo str_repeat('  ', $level) . "- {$point->name} ({$duration}s) data: {$data}\n";
            $this->printPoints($point->children, $level + 1);
        }
    }

    public function printLog(): void
    {
        foreach ($this->log as $entry) {
            $time = number_format($entry['time'], 6);
            $indent = str_repeat('  ', $entry['depth']);
            $data = isset($entry['data'])
                ? json_encode($entry['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : ''
            ;
            $dataStr = $data ? " data: {$data}" : '';
            echo "{$indent}[{$time}] {$entry['action']} '{$entry['point']}'{$dataStr}\n";
        }
    }

    private function logToFile(array $entry): void
    {
        if (!$this->logToFile) {
            return;
        }

        $time = number_format($entry['time'], 6);
        $indent = str_repeat('  ', $entry['depth']);
        $data = isset($entry['data'])
            ? json_encode($entry['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : ''
        ;
        $dataStr = $data ? " data: {$data}" : '';
        $line = "{$indent}[{$time}] {$entry['action']} '{$entry['point']}'{$dataStr}\n";

        file_put_contents($this->logFilePath, $line, FILE_APPEND);
    }
}
