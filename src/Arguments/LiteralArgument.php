<?php

namespace Vengine\Libs\DI\Arguments;

use InvalidArgumentException;
use Vengine\Libs\DI\interfaces\LiteralArgumentInterface;
use Vengine\Libs\DI\Storage\ArgumentTypeStorage;

class LiteralArgument extends AbstractBaseArgument implements LiteralArgumentInterface
{
    public function __construct(mixed $value, ?string $type = null)
    {
        if (
            $type === null
            || ($type === ArgumentTypeStorage::TYPE_CALLABLE && is_callable($value))
            || ($type === ArgumentTypeStorage::TYPE_OBJECT && is_object($value))
            || gettype($value) === $type
        ) {
            $this->value = $value;
        } else {
            throw new InvalidArgumentException('Incorrect type for value.');
        }
    }
}
