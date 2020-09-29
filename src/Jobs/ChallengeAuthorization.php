<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ChallengeAuthorization implements ShouldQueue
{
    use Dispatchable;

    /** @var AuthorizationChallenge */
    protected $challenge;

    public function __construct(AuthorizationChallenge $httpChallenge)
    {
        $this->challenge = $httpChallenge;
    }

    public function handle()
    {
        $client = LetsEncrypt::createClient();
        $client->challengeAuthorization($this->challenge);
        CleanUpChallenge::dispatch($this->challenge);
    }
}
