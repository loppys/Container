<?php

use Vengine\Libs\Definitions\Definition;
use Vengine\Libs\Definitions\DefinitionAggregate;
use Vengine\Libs\config\Configure;
use Vengine\Libs\Container;

class ContainerTest extends Container
{
    public function __construct()
    {
        parent::__construct(
            new Configure(),
            new DefinitionAggregate([new TestDefinition()])
        );
    }
}

class TestDefinition extends Definition
{
    public function __construct(
        protected string $id = 'test',
        protected mixed  $concrete = TestClass::class,
        protected bool   $shared = false,
        protected array  $arguments = [],
        protected array  $tempArguments = [],
        protected array  $methods = [],
        protected array  $sharedTags = [],
    ) {
        $this->setId($this->id);
        $this->concrete ??= $this->id;
    }
}

class TestClass
{
    public function isCreated(): bool
    {
        return true;
    }
}