<?php

namespace Daanra\LaravelLetsEncrypt\Builders;

use Illuminate\Database\Eloquent\Builder;

class LetsEncryptCertificateBuilder extends Builder
{
    public function expired(): self
    {
        return $this->where('last_renewed_at', '<=', now()->subDays(90));
    }

    public function valid(): self
    {
        return $this->where('last_renewed_at', '>', now()->subDays(90));
    }

    public function requiresRenewal(): self
    {
        return $this->where('last_renewed_at', '<=', now()->subDays(61));
    }
}
