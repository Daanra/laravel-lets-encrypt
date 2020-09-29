<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;

class RenewExpiringCertificates implements ShouldQueue
{
    use Dispatchable;

    public function __construct()
    {
    }

    public function handle()
    {
        LetsEncryptCertificate::query()
            ->requiresRenewal()
            ->chunk(100, function (Collection $certificates) {
                $certificates->each(function (LetsEncryptCertificate $certificate) {
                    LetsEncrypt::renew($certificate);
                });
            });
    }
}
