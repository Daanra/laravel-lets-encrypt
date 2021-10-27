<?php

namespace Daanra\LaravelLetsEncrypt\Interfaces;

interface LetsEncryptCertificateFailed
{
    public function getException(): \Throwable;
}
