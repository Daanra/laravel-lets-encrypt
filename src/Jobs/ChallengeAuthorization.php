<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ChallengeAuthorization implements ShouldQueue
{
    use Dispatchable;

    /** @var AuthorizationChallenge */
    protected $challenge;

    /** @var AcmeClient */
    protected $client;

    public function __construct(AcmeClient $client, AuthorizationChallenge $httpChallenge)
    {
        $this->client = $client;
        $this->challenge = $httpChallenge;
    }

    public function handle()
    {
        $this->client->challengeAuthorization($this->challenge);
        CleanUpChallenge::dispatch($this->challenge);
    }
}
