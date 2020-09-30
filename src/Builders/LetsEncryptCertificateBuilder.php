<?php

namespace Daanra\LaravelLetsEncrypt\Builders;

use Illuminate\Database\Eloquent\Builder;

class LetsEncryptCertificateBuilder extends Builder
{
    /**
     * Returns all certificates that have expired, i.e. all certificates that have been issues more than 90 days ago.
     * @return self
     */
    public function expired(): self
    {
        return $this->where('last_renewed_at', '<=', now()->subDays(90));
    }

    /**
     * Returns all certificates that are current valid, i.e. certificates that have been issues less than 90 days
     * ago.
     * @return self
     */
    public function valid(): self
    {
        return $this->where('last_renewed_at', '>', now()->subDays(90));
    }

    /**
     * Returns all certificates that require renewal, i.e. all certificates that are older than 60 days.
     * @return self
     */
    public function requiresRenewal(): self
    {
        return $this->where('last_renewed_at', '<=', now()->subDays(61));
    }
}
