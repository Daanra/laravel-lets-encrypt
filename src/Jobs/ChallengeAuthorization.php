<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChallengeAuthorization implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /** @var AuthorizationChallenge */
    protected $challenge;

    public function __construct(AuthorizationChallenge $httpChallenge)
    {
        $this->challenge = $httpChallenge;
    }

    /**
     * Tells the LetsEncrypt API that our challenge is in place. LetsEncrypt will attempt to access
     * the challenge on <domain>/.well-known/acme-challenges/<token>
     * If this job succeeds, we can clean up the challenge and request a certificate.
     * @throws \Daanra\LaravelLetsEncrypt\Exceptions\InvalidKeyPairConfiguration
     */
    public function handle()
    {
        $client = LetsEncrypt::createClient();
        $client->challengeAuthorization($this->challenge);
        CleanUpChallenge::dispatch($this->challenge);
    }
}
