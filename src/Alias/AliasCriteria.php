<?php

namespace Vengine\Libs\Alias;

class AliasCriteria
{
    public function __construct(
        protected string $group = '',
        protected string $id = '',
        protected string $aliasName = '',
        protected PriorityCriteria $priority = new PriorityCriteria()
    ) {
    }

    public function getAliasName(): string
    {
        return $this->aliasName;
    }

    public function getPriority(): PriorityCriteria
    {
        return $this->priority;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGroup(): string
    {
        return $this->group;
    }
}
