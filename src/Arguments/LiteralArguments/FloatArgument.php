<?php

namespace Vengine\Libs\DI\Arguments\LiteralArguments;

use Vengine\Libs\DI\Arguments\LiteralArgument;
use Vengine\Libs\DI\Storage\ArgumentTypeStorage;

class FloatArgument extends LiteralArgument
{
    public function __construct(mixed $value)
    {
        parent::__construct($value, ArgumentTypeStorage::TYPE_FLOAT);
    }
}
