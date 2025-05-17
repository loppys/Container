<?php

namespace Vengine\Libs\Alias;

class PriorityCriteria
{
    public function __construct(
        protected int|array|null $priority = null,
        protected string $operation = PriorityCriteriaOperations::EQUALS
    ) {
    }

    public function getPriority(): array|int|null
    {
        return $this->priority;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }
}
