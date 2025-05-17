<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\ServiceCollectors\ArrayServiceCollector;
use TestContainer;
use TestInflectorInterface;
use TestClass;

/**
 * @group inflector
 */
class InflectorTest extends TestCase
{
    /**
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function testInflectorObject(): void
    {
        $di = new TestContainer();

        $di->collect(new ArrayServiceCollector([
            'test.inflector' => [
                'class' => TestClass::class,
            ],
        ]));

        $di->inflector(TestInflectorInterface::class, static function (TestClass $testClass) {
            $testClass->changeTestPropertyValue('hi');
        });

        /** @var TestClass $test */
        $test = $di->get('test.inflector');

        $this->assertTrue($test->getTestProperty() === 'hi');
    }
}
