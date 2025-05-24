<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Vengine\Libs\DI\interfaces\ContainerInterface;
use Vengine\Libs\DI\Packages\AbstractPackage;
use TestContainer;

class PackageTest extends TestCase
{
    public function testPackage()
    {
        $di = new TestContainer();
        $di->addPackage($this->getTestPackage());

        $this->assertTrue($di->callPackage('test.package') instanceof AbstractPackage);
    }

    protected function getTestPackage(): AbstractPackage
    {
        return new class extends AbstractPackage
        {
            protected string $name = 'test.package';

            public function call(ContainerInterface $container): mixed
            {
                return $this;
            }
        };
    }
}
