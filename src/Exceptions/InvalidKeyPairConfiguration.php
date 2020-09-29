<?php

namespace Daanra\LaravelLetsEncrypt\Exceptions;

use Exception;

class InvalidKeyPairConfiguration extends Exception
{
    public function __construct($message)
    {
        $message .= '\nConfiguration:\nPublic key path: '
            . config('lets_encrypt.public_key_path', storage_path('app/lets-encrypt/keys/account.pub.pem'))
            . '\nPrivate key path: '
            . config('lets_encrypt.private_key_path', storage_path('app/lets-encrypt/keys/account.pem'));

        parent::__construct($message);
    }
}
