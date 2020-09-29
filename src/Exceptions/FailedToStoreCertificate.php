<?php

namespace Daanra\LaravelLetsEncrypt\Exceptions;

use Exception;

class FailedToStoreCertificate extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct('Failed to store certificate at path \'' . $path . '\'');
    }
}
