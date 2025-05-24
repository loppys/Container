<?php

namespace Vengine\Libs\DI\Profiling;

use Vengine\Libs\DI\traits\ArgumentResolverTrait;
use Vengine\Libs\DI\traits\ContainerAwareTrait;
use Closure;

class ProfilingEventHandler
{
    use ArgumentResolverTrait;
    use ContainerAwareTrait;

    protected bool $enabled = false;
    /**
     * @var Closure[]
     */
    protected array $events = [];
    /**
     * @var Closure[]
     */
    protected array $preHandlers = [];

    public function handle(int $type, mixed $data = null): void
    {
        if (!$this->enabled) {
            return;
        }

        if (empty($this->events[$type])) {
            return;
        }

        foreach ($this->events[$type] as $name => $event) {
            if (!is_callable($event)) {
                continue;
            }

            if (!empty($this->preHandlers[$type]) && !empty($this->preHandlers[$type][$name])) {
                [$name, $data] = $this->preHandlers[$type][$name]($name, $data);
            }

            if (!is_null($data)) {
                $event($name, $data);
            } else {
                $event($name);
            }
        }
    }

    public function addEvent(
        string $name,
        int $type,
        callable $closure,
        ?callable $preHandler = null
    ): static {
        $this->events[$type][$name] = $closure;

        if (!is_null($preHandler)) {
            $this->preHandlers[$type][$name] = $preHandler;
        }

        return $this;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
