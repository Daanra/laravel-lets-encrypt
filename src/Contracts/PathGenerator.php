<?php

namespace Daanra\LaravelLetsEncrypt\Contracts;

use AcmePhp\Core\Protocol\AuthorizationChallenge;

/**
 * Interface for when you override the DefaultPathGenerator.
 *
 * NOTE: If you do override the DefaultPathGenerator, you are responsible for defining a controller
 * that returns the content of the file located at the path. So you should define a route that looks like this:
 * Route::get('/.well-known/acme-challenge/{token}, function ($token) {
 *     return Storage::get(app(MyCustomPathGenerator)->getPath($token));
 * });
 */
interface PathGenerator
{
    // Should return the path of where the challenge should be stored.
    public function getPath(string $token): string;
}