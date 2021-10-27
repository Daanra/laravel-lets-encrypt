<?php

namespace Daanra\LaravelLetsEncrypt\Facades;

use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\PendingCertificate;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Daanra\LaravelLetsEncrypt\LetsEncrypt
 * @method static array create(string $domain, array $chain = [])
 * @method static LetsEncryptCertificate createNow(string $domain)
 * @method static LetsEncryptCertificate renewNow(string|LetsEncryptCertificate $domain)
 * @method static PendingDispatch renew(string|LetsEncryptCertificate $domain, array $chain = [])
 * @method static PendingCertificate certificate(string $domain)
 */
class LetsEncrypt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lets-encrypt';
    }
}
