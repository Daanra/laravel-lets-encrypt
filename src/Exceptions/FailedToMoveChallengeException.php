<?php

namespace Daanra\LaravelLetsEncrypt\Exceptions;

use Exception;

class FailedToMoveChallengeException extends Exception
{
    public function __construct($path)
    {
        parent::__construct('Failed to create lets-encrypt challenge at path \'' . $path . '\'.');
    }
}
