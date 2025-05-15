<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use ContainerTest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TestClass;
use TestDefClass;
use Vengine\Libs\Exceptions\ContainerException;
use Vengine\Libs\Exceptions\NotFoundException;
use Vengine\Libs\ServiceCollectors\ArrayServiceCollector;

/**
 * @group definition
 */
class DefinitionTest extends TestCase
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testCreateServices(): void
    {
        $testContainer = new ContainerTest();

        $testClass = $testContainer->get(TestClass::class);
        $this->assertTrue($testClass instanceof TestClass);

        $testContainer = new ContainerTest();

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
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     */
    public function testChangeProperties(): void
    {
        $testContainer = new ContainerTest();

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

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testSpeed1000(): void
    {
        $start = microtime(true);
        $testContainer = new ContainerTest();
        $testContainer->collect(new ArrayServiceCollector($this->getServicesRaw(1000)));

        $i = 0;
        while ($i < 1000) {
            $testContainer->get('test_' . $i);

            $i++;
        }

        // Погрешность +0.75сек для пайплайна. Локально отрабатывает за ~0.25сек
        $this->assertLessThanOrEqual(1, microtime(true) - $start);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     */
    public function testSpeed10000(): void
    {
        $start = microtime(true);

        $testContainer = new ContainerTest();
        $testContainer->collect(new ArrayServiceCollector($this->getServicesRaw(10000)));

        $i = 0;
        while ($i < 10000) {
            $testContainer->get('test_' . $i);

            $i++;
        }

        // Погрешность +80сек для пайплайна. Локально отрабатывает за ~20сек
        $this->assertLessThanOrEqual(100, microtime(true) - $start);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testClosureSpeed1000(): void
    {
        $start = microtime(true);

        $testContainer = new ContainerTest();
        $testContainer->collect(new ArrayServiceCollector($this->getServicesRaw(1000, true)));

        $i = 0;
        while ($i < 1000) {
            $testContainer->get('test_' . $i);

            $i++;
        }

        // Погрешность +0.75сек для пайплайна. Локально отрабатывает за ~0.25сек
        $this->assertLessThanOrEqual(1, microtime(true) - $start);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testClosureSpeed10000(): void
    {
        $start = microtime(true);

        $testContainer = new ContainerTest();
        $testContainer->collect(new ArrayServiceCollector($this->getServicesRaw(10000, true)));

        $i = 0;
        while ($i < 10000) {
            $testContainer->get('test_' . $i);

            $i++;
        }

        // Погрешность +80сек для пайплайна. Локально отрабатывает за ~20сек
        $this->assertLessThanOrEqual(100, microtime(true) - $start);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     */
    public function testDefaultLessClosure(): void
    {
        $testContainer = new ContainerTest();
        $testContainer->collect(new ArrayServiceCollector($this->getServicesRaw(3000, true)));

        $i = 0;
        $startClosure = microtime(true);
        while ($i < 1000) {
            $testContainer->get('test_' . $i);

            $i++;
        }
        $endClosure = microtime(true) - $startClosure;

        $testContainer->collect(new ArrayServiceCollector($this->getServicesRaw(3000)));

        $i = 0;
        $startDefault = microtime(true);
        while ($i < 1000) {
            $testContainer->get('test_' . $i);

            $i++;
        }
        $endDefault = microtime(true) - $startDefault;

        codecept_debug(['closure' => $endClosure, 'default' => $endDefault]);

        $this->assertTrue($endClosure > $endDefault);
    }

    protected function getServicesRaw(int $limit = 100, bool $closure = false): array
    {
        $res = [];

        $res['test.def.class'] = [
            'class' => TestDefClass::class,
            'shared' => true,
        ];

        $i = 0;
        while ($i < $limit) {
            if ($closure) {
                $rr = [
                    'closure' => static function (TestDefClass $defClass) {
                        return new TestClass($defClass);
                    },
                    'arguments' => [
                        'defClass' => '@test.def.class',
                    ],
                ];
            } else {
                $rr = [
                    'class' => TestClass::class,
                ];
            }

            $res['test_' . $i] = $rr;

            $i++;
        }

        return $res;
    }
}
