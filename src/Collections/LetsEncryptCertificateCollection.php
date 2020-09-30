<?php

namespace Daanra\LaravelLetsEncrypt\Collections;

use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Illuminate\Database\Eloquent\Collection;

class LetsEncryptCertificateCollection extends Collection
{
    /**
     * Places a job on the queue for each certificate to renew it.
     * @return self
     */
    public function renew(): self
    {
        $this->each(function (LetsEncryptCertificate $certificate): void {
            $certificate->renew();
        });

        return $this;
    }

    /**
     * Renews all certificates in the collection synchronously (without placing them on the queue).
     * @return self
     */
    public function renewNow(): self
    {
        $this->each(function (LetsEncryptCertificate $certificate): void {
            $certificate->renewNow();
        });

        return $this;
    }
}
