<?php

namespace Loader\System\Exceptions;

use RuntimeException;
use Throwable;

class ClassNotFoundException extends RuntimeException
{
    public function __construct($class = '', $code = 500, Throwable $previous = null)
    {
        if (!empty($class)) {
            $message = "{$class} not found";
        } else {
            $message = 'Class not found';
        }

        parent::__construct($message, $code, $previous);
    }
}
