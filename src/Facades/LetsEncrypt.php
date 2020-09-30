<?php

namespace Daanra\LaravelLetsEncrypt\Facades;

use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Daanra\LaravelLetsEncrypt\LetsEncrypt
 * @method static PendingDispatch create(string $domain)
 * @method static LetsEncryptCertificate createNow(string $domain)
 * @method static LetsEncryptCertificate renewNow($domain)
 * @method static PendingDispatch renew($domain)
 */
class LetsEncrypt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lets-encrypt';
    }
}
