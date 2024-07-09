<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Events\ChallengeAuthorizationFailed;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Traits\Retryable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChallengeAuthorization implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, Retryable;

    /**
     * @var AuthorizationChallenge
     */
    protected $challenge;


    public function __construct(AuthorizationChallenge $httpChallenge, int $tries = null, int $retryAfter = null, array $retryList = [])
    {
        $this->challenge = $httpChallenge;
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
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
        CleanUpChallenge::dispatch($this->challenge, $this->tries, $this->retryAfter, $this->retryList);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        event(new ChallengeAuthorizationFailed($exception, $this->challenge));
    }
}
