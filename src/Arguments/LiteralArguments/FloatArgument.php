<?php

namespace Vengine\Libs\Arguments\LiteralArguments;

use Vengine\Libs\Arguments\LiteralArgument;
use Vengine\Libs\Storage\ArgumentTypeStorage;

class FloatArgument extends LiteralArgument
{
    public function __construct(mixed $value)
    {
        parent::__construct($value, ArgumentTypeStorage::TYPE_FLOAT);
    }
}
