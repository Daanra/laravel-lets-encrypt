<?php

namespace Daanra\LaravelLetsEncrypt\Exceptions;

use Exception;

class InvalidPathGenerator extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct($class . ' is not a valid PathGenerator.');
    }
}
