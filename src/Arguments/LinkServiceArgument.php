<?php

namespace Vengine\Libs\Arguments;

use Vengine\Libs\interfaces\LinkServiceArgumentInterface;

class LinkServiceArgument extends AbstractBaseArgument implements LinkServiceArgumentInterface
{
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}
