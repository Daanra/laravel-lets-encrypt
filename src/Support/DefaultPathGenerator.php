<?php

namespace Daanra\LaravelLetsEncrypt\Support;

use Daanra\LaravelLetsEncrypt\Contracts\PathGenerator;

class DefaultPathGenerator implements PathGenerator
{
    public function getPath(string $token): string
    {
        return 'public/.well-known/acme-challenge/' . $token;
    }
}
