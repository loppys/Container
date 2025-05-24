<?php

namespace Vengine\Libs\DI\Profiling;

class TimerPoint
{
    public string $name;

    public mixed $data;

    public float $start;

    public ?float $end = null;

    /**
     * @var TimerPoint[]
     */
    public array $children = [];

    public function __construct(string $name, mixed $data)
    {
        $this->name = $name;
        $this->data = $data;
        $this->start = microtime(true);
    }

    public function end(): void
    {
        if ($this->end === null) {
            $this->end = microtime(true);
        }

        foreach ($this->children as $child) {
            $child->end();
        }
    }

    public function duration(): ?float
    {
        return $this->end !== null ? $this->end - $this->start : null;
    }
}
