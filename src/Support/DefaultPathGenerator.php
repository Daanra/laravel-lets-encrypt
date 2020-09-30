<?php

namespace Daanra\LaravelLetsEncrypt\Support;

use Daanra\LaravelLetsEncrypt\Contracts\PathGenerator;

class DefaultPathGenerator implements PathGenerator
{
    public function getChallengePath(string $token): string
    {
        return 'public/.well-known/acme-challenge/' . $token;
    }

    public function getCertificatePath(string $domain, string $filename): string
    {
        return 'letsencrypt/certificates/' . $domain . '/' . $filename;
    }
}
