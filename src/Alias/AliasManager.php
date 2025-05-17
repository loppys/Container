<?php

namespace Vengine\Libs\Alias;

use Iterator;

class AliasManager implements Iterator
{
    /** @var Alias[] */
    private array $aliasMap = [];

    /** @var string[] */
    private array $sortedAliases = [];
    private int $cursor = 0;

    /** @var Alias[][] */
    private array $groupAliases = [];

    private bool $skipSorting = false;

    /**
     * @param Alias[] $initial
     */
    public function __construct(Alias ...$initial)
    {
        $this->addList(...$initial);
    }

    public function addList(Alias ...$dataList): void
    {
        $this->skipSorting = true;

        foreach ($dataList as $alias) {
            $this->add($alias);
        }

        $this->skipSorting = false;

        $this->sortAliases();
        $this->compareGroupAliases();
    }

    public function add(Alias $alias): Alias
    {
        $this->aliasMap[$alias->getName()] = $alias;

        if (!$this->skipSorting) {
            $this->sortAliases();
            $this->compareGroupAliases();
        }

        return $alias;
    }

    public function remove(string $aliasName): void
    {
        unset($this->aliasMap[$aliasName]);

        if (!$this->skipSorting) {
            $this->sortAliases();
            $this->compareGroupAliases();
        }
    }

    public function getAlias(AliasCriteria $criteria): ?Alias
    {
        $group = $criteria->getGroup();
        $id = $criteria->getId();
        $aliasName = $criteria->getAliasName();
        $priorityCriteria = $criteria->getPriority();

        if (!empty($aliasName)) {
            foreach ($this->aliasMap as $alias) {
                if (
                    $alias->getAliasName() === $aliasName
                    && $this->processSortByPriority($alias->getPriority(), $priorityCriteria)
                ) {
                    return $alias;
                }
            }
        }

        if (!empty($group)) {
            foreach ($this->groupAliases[$group] as $groupAlias) {
                if (
                    $groupAlias->getName() === $criteria->getId()
                    || $this->processSortByPriority($groupAlias->getPriority(), $priorityCriteria)
                ) {
                    return $groupAlias;
                }
            }

            $group = null;
        }

        if (empty($group) && !empty($id)) {
            foreach ($this->aliasMap as $alias) {
                if (
                    $alias->getName() === $criteria->getId()
                    || $this->processSortByPriority($alias->getPriority(), $priorityCriteria)
                ) {
                    return $alias;
                }
            }
        }

        return null;
    }

    private function processSortByPriority(int $value, PriorityCriteria $criteria): bool
    {
        if ($value === 0) {
            return true;
        }

        $priorityFromCriteria = $criteria->getPriority();
        if (is_null($priorityFromCriteria)) {
            return true;
        }

        $betweenOperation = $criteria->getOperation() === PriorityCriteriaOperations::BETWEEN;
        if (
            (is_array($priorityFromCriteria) && !$betweenOperation)
            || (!is_array($priorityFromCriteria) && $betweenOperation)
        ) {
            return false;
        }

        switch ($criteria->getOperation()) {
            case PriorityCriteriaOperations::EQUALS:
                return $value === $priorityFromCriteria;
            case PriorityCriteriaOperations::MORE:
                return $value > $priorityFromCriteria;
            case PriorityCriteriaOperations::LESS:
                return $value < $priorityFromCriteria;
            case PriorityCriteriaOperations::BETWEEN:
                $first = $priorityFromCriteria[0] ?? 0;
                $two = $priorityFromCriteria[1] ?? 1;

                return $value > $first && $value < $two;
        }

        return false;
    }

    public function getGroupKey(AliasCriteria $criteria): ?string
    {
        return $this->getAlias($criteria)?->getGroupKey() ?? null;
    }

    /**
     * @return Alias[]
     */
    public function getAliasesByGroup(AliasCriteria $criteria): array
    {
        if (empty($this->groupAliases)) {
            $this->compareGroupAliases();
        }

        return $this->groupAliases[$criteria->getGroup()] ?? [];
    }

    public function current(): Alias
    {
        return $this->aliasMap[$this->sortedAliases[$this->cursor]];
    }

    public function key(): string
    {
        return $this->sortedAliases[$this->cursor];
    }

    public function next(): void
    {
        $this->cursor++;
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }

    public function valid(): bool
    {
        return isset($this->sortedAliases[$this->cursor]);
    }

    /**
     * @see глобальная сортировка
     */
    private function sortAliases(): void
    {
        uasort($this->aliasMap, fn(Alias $a, Alias $b) => $b->getPriority() <=> $a->getPriority());
        $this->sortedAliases = array_keys($this->aliasMap);

        $this->rewind();
    }

    /**
     * @see сортировка внутри групп
     */
    private function sortGroupAliases(): void
    {
        foreach ($this->groupAliases as $k => $i) {
            uasort($this->groupAliases[$k], fn(Alias $a, Alias $b) => $b->getPriority() <=> $a->getPriority());
        }
    }

    private function compareGroupAliases(): void
    {
        foreach ($this->aliasMap as $alias) {
            $this->groupAliases[$alias->getGroupKey()][$alias->getName()] = $alias;
        }

        $this->sortGroupAliases();
    }

    public function all(): array
    {
        return array_values($this->aliasMap);
    }
}
