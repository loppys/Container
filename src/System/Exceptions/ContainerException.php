<?php

namespace Loader\System\Exceptions;

use RuntimeException;
use Throwable;

class ContainerException extends RuntimeException
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
