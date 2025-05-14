<?php

namespace Vengine\Libs\Alias;

class AliasCriteria
{
    public function __construct(
        protected string $group = '',
        protected string $name = '',
        protected PriorityCriteria $priority = new PriorityCriteria()
    ) {
    }

    public function getPriority(): PriorityCriteria
    {
        return $this->priority;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGroup(): string
    {
        return $this->group;
    }
}
