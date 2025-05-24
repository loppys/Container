<?php

namespace Vengine\Libs\DI\Arguments;

use Vengine\Libs\DI\interfaces\LinkServiceArgumentInterface;

class LinkServiceArgument extends AbstractBaseArgument implements LinkServiceArgumentInterface
{
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}
