<?php

namespace Daanra\LaravelLetsEncrypt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Daanra\LaravelLetsEncrypt\LetsEncrypt
 */
class LetsEncrypt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lets-encrypt';
    }
}
