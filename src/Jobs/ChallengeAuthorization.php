<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Events\ChallengeAuthorizationFailed;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChallengeAuthorization implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /**
     * @var AuthorizationChallenge
     */
    protected $challenge;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter;

    /**
     * The list of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $retryList;


    public function __construct(AuthorizationChallenge $httpChallenge, int $tries = null, int $retryAfter = null, $retryList = [])
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
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function retryAfter()
    {
        return (!empty($this->retryList)) ? $this->retryList[$this->attempts() - 1] : 0;
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        event(new ChallengeAuthorizationFailed($this));
    }
}
