<?php

namespace Daanra\LaravelLetsEncrypt\Exceptions;

use Exception;

class InvalidDomainException extends Exception
{
    public function __construct($domain)
    {
        parent::__construct('The domain \'' . $domain . '\' is not a valid domain. A domain may not contain characters such as \':\' and \'/\'.');
    }
}
