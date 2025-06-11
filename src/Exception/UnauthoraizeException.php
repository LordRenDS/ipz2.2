<?php

namespace Ren\App\Exception;

use Exception;

class UnauthoraizeException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
