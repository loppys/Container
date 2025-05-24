<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libs\DI\Alias\Alias;
use Vengine\Libs\DI\Alias\AliasCriteria;
use Vengine\Libs\DI\Alias\AliasManager;
use Vengine\Libs\DI\Alias\PriorityCriteria;
use Vengine\Libs\DI\Alias\PriorityCriteriaOperations;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use TestContainer;
use TestClass;
use TestDefClass;

/**
 * @group aliases
 */
class AliasTest extends TestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped(
            'реализация пока такая себе, надо придумать более нормальную реализацию алиас сервисов'
        );
    }

    /**
     * @dataProvider getDataForMainOperations
     */
    public function testMainOperations(array $aliases): void
    {
        $manager = $this->getAliasManager($aliases);

        $this->assertCount(3, $manager->getAliasesByGroup(new AliasCriteria('group3')));
        $this->assertEquals('test1', $manager->getAlias(new AliasCriteria('group1'))?->getName());

        $this->assertCount(3, $manager->getAliasesByGroup(new AliasCriteria('group3')));

        $alias = $manager->getAlias(new AliasCriteria('group2', 'test2'));
        $this->assertEquals('test2', $alias->getName());

        $manager->add(new Alias('test5', 'test5', 'group3', 5));
        $alias = $manager->getAlias(new AliasCriteria(id: 'test5'));
        $this->assertTrue($alias->getGroupKey() === 'group3' && $alias->getPriority() === 5);
    }

    /**
     * @dataProvider getAliasesPriority
     */
    public function testPriorityAliases(array $aliases): void
    {
        $manager = $this->getAliasManager($aliases);

        $alias = $manager->getAlias(
            new AliasCriteria(
                'alias_priority',
                priority: new PriorityCriteria([25, 94], PriorityCriteriaOperations::BETWEEN)
            )
        );
        $this->assertEquals('Alias2', $alias->getName());

        $alias = $manager->getAlias(
            new AliasCriteria(
                'alias_priority',
                priority: new PriorityCriteria(125, PriorityCriteriaOperations::EQUALS)
            )
        );
        $this->assertEquals('Alias4', $alias->getName());

        $alias = $manager->getAlias(
            new AliasCriteria(
                'alias_priority',
                priority: new PriorityCriteria(125, PriorityCriteriaOperations::MORE)
            )
        );
        $this->assertEquals('Alias5', $alias->getName());

        $alias = $manager->getAlias(
            new AliasCriteria(
                'alias_priority',
                priority: new PriorityCriteria(1000, PriorityCriteriaOperations::LESS)
            )
        );
        $this->assertEquals('Alias5', $alias->getName());
    }

    /**
     * @dataProvider getAliasesGroups
     */
    public function testAliasesGroups(array $aliases): void
    {
        $manager = $this->getAliasManager($aliases);

        $alias = $manager->getAlias(new AliasCriteria(
            'group3',
            'testAlias3'
        ));
        $this->assertEquals(5, $alias->getPriority());

        //Берет первый алиас в последней добавленной группе
        $alias = $manager->getAlias(new AliasCriteria(
            id: 'testAlias1'
        ));
        $this->assertEquals('group3', $alias->getGroupKey());
    }

    protected function getDataForMainOperations(): array
    {
        return [
            [
                [
                    new Alias('test1', 'test1', 'group1', 1),
                    new Alias('test2', 'test2', 'group2', 1),
                    new Alias('test3', 'test3', 'group2', 1),
                    new Alias('test3', 'test3', 'group3', 1),
                    new Alias('test4', 'test4', 'group3', 1),
                    new Alias('test5', 'test5', 'group3', 1),
                ]
            ]
        ];
    }

    protected function getAliasesPriority(): array
    {
        return [
            [
                [
                    new Alias('Alias1', 'Alias1', 'alias_priority', 100),
                    new Alias('Alias2', 'Alias2', 'alias_priority', 55),
                    new Alias('Alias3', 'Alias3', 'alias_priority', 23),
                    new Alias('Alias4', 'Alias4', 'alias_priority', 125),
                    new Alias('Alias5', 'Alias5', 'alias_priority', 511),
                ],
            ],
        ];
    }

    protected function getAliasesGroups(): array
    {
        return [
            [
                [
                    new Alias('testAlias1', 'testAlias1', 'group1', 1),
                    new Alias('testAlias1', 'testAlias1', 'group2', 1),
                    new Alias('testAlias2', 'testAlias2', 'group2', 1),
                    new Alias('testAlias1', 'testAlias1', 'group3', 1),
                    new Alias('testAlias2', 'testAlias2', 'group3', 1),
                    new Alias('testAlias3', 'testAlias3', 'group3', 1),
                    new Alias('testAlias3', 'testAlias3', 'group3', 5),
                ]
            ],
        ];
    }

    protected function getAliasManager(array $aliases): AliasManager
    {
        return new AliasManager(...$aliases);
    }
}
