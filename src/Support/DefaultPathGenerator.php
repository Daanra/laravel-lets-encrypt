<?php

namespace Daanra\LaravelLetsEncrypt\Support;

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Contracts\PathGenerator;

class DefaultPathGenerator implements PathGenerator
{
    public function getPath(string $token): string
    {
        return 'app/public/.well-known/acme-challenge/' . $token;
    }
}