<?php

namespace Daanra\LaravelLetsEncrypt\Contracts;

/**
 * Interface for when you override the DefaultPathGenerator.
 *
 * NOTE: If you do override the DefaultPathGenerator's getChallengePath, you are responsible for defining a controller
 * that returns the content of the file located at the path. So you should define a route that looks like this:
 * Route::get('/.well-known/acme-challenge/{token}, function ($token) {
 *     return Storage::get(app(MyCustomPathGenerator)->getChallengePath($token));
 * });
 */
interface PathGenerator
{
    // Should return the path of where the challenge should be stored.
    public function getChallengePath(string $token): string;

    // Should return the path of where the certificate should be stored.
    // Note that $filename is 'privkey.pem', 'fullchain.pem', 'chain.pem' or 'cert.pem'
    public function getCertificatePath(string $domain, string $filename): string;
}
