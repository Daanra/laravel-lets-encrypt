<?php

namespace Daanra\LaravelLetsEncrypt\Exceptions;

use Exception;

class DomainAlreadyExists extends Exception
{
    public function __construct(string $domain)
    {
        parent::__construct('The \'' . $domain . '\' already exists in the database.');
    }
}
