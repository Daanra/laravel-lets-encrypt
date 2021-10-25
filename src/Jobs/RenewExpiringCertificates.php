<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use Daanra\LaravelLetsEncrypt\Collections\LetsEncryptCertificateCollection;
use Daanra\LaravelLetsEncrypt\Events\RenewExpiringCertificatesFailed;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\Traits\JobTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenewExpiringCertificates implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, JobTrait;

    public function __construct(int $tries = null, int $retryAfter = null, $retryList = [])
    {
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
    }

    public function handle()
    {
        LetsEncryptCertificate::query()
            ->requiresRenewal()
            ->chunk(100, function (LetsEncryptCertificateCollection $certificates) {
                $certificates->renew();
            });
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        event(new RenewExpiringCertificatesFailed($this));
    }
}
