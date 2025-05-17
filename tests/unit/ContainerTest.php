<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use TestContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TestClass;
use TestDefClass;
use Vengine\Cache\Drivers\AbstractDriver;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\ServiceCollectors\ArrayServiceCollector;

/**
 * @group definition
 */
class ContainerTest extends TestCase
{
    /**
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function testReplaceDefinition(): void
    {
        $di = new TestContainer();

        $di->collect(new ArrayServiceCollector([
            'test.replace' => [
                'class' => TestClass::class
            ],
            TestDefClass::class => [
                'class' => TestDefClass::class,
            ],
            '@@' . TestDefClass::class => [
                'closure' => static function () {
                    $t = new TestDefClass();
                    $t->test = '666';

                    return $t;
                }
            ],
        ]));

        /** @var TestClass $test */
        $test = $di->get('test.replace');
        $this->assertEquals('666', $test->getTestProperty());
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testCreateServices(): void
    {
        $testContainer = new TestContainer();

        $testClass = $testContainer->get(TestClass::class);
        $this->assertTrue($testClass instanceof TestClass);

        $testContainer = new TestContainer();

        $testDefClass = $testContainer->get(TestDefClass::class);
        $this->assertTrue($testDefClass instanceof TestDefClass);

        $testDefClass->test = '123';
        $testClass = $testContainer->get(TestClass::class);
        $this->assertEquals('123', $testClass->getTestProperty());

        $raw = [
            'class' => TestClass::class,
            'calls' => [
                'changeTestPropertyValue' => ['newValue' => '192']
            ],
        ];

        $testContainer->addRawService('test192', $raw);

        /** @var TestClass $test192 */
        $test192 = $testContainer->get('test192');
        $this->assertTrue($test192 instanceof TestClass);
        $this->assertEquals('192', $test192->getTestProperty());

        $test192->changeTestPropertyValue('999');
        $this->assertEquals('999', $test192->getTestProperty());

        /** @var TestClass $testNew */
        $testNew = $testContainer->getNew('test192');
        $this->assertEquals('192', $testNew->getTestProperty());

        $testContainer->add('test.def', static function (?string $replace = null) {
            $def = new TestDefClass();
            if (!is_null($replace)) {
                $def->test = $replace;
            } else {
                $def->test = '111';
            }

            return $def;
        });

        /** @var TestDefClass $testDef */
        $testDef = $testContainer->get('test.def');
        $this->assertTrue($testDef->test === '111');

        /** @var TestDefClass $testDef */
        $testDef = $testContainer->getNewWithArguments('test.def', ['replace' => '666']);
        $this->assertEquals('666', $testDef->test);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function testChangeProperties(): void
    {
        $testContainer = new TestContainer();

        $rawServices = [
            'test.first' => [
                'class' => TestDefClass::class,
                'properties' => [
                    'test' => '12234',
                ],
            ],
            'test.two' => [
                'closure' => static function () {
                    return new TestDefClass();
                },
                'properties' => [
                    'test' => '999',
                ],
            ],
        ];

        $testContainer->collect(new ArrayServiceCollector($rawServices));

        /** @var TestDefClass $testFirst */
        $testFirst = $testContainer->get('test.first');
        $this->assertEquals('12234', $testFirst->test);

        /** @var TestDefClass $testFirst */
        $testFirst = $testContainer->get('test.two');
        $this->assertEquals('999', $testFirst->test);
    }
}
