<?php

use Vengine\Libs\DI\Definitions\Definition;
use Vengine\Libs\DI\Definitions\DefinitionAggregate;
use Vengine\Libs\DI\config\Configure;
use Vengine\Libs\DI\Container;

class TestContainer extends Container
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

interface TestInflectorInterface
{

}

class TestDefClass
{
    public string $test = '888';
}

class TestClass implements TestInflectorInterface
{
    protected TestDefClass $testDefClass;

    public function __construct(TestDefClass $testDefClass)
    {
        $this->testDefClass = $testDefClass;
    }

    public function getTestProperty(): string
    {
        return $this->testDefClass->test;
    }

    public function changeTestPropertyValue(string $newValue): void
    {
        $this->testDefClass->test = $newValue;
    }
}