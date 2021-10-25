<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Events\CleanUpChallengeFailed;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Daanra\LaravelLetsEncrypt\Traits\JobTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanUpChallenge implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, JobTrait;

    /** @var AuthorizationChallenge */
    protected $challenge;

    /** @var AcmeClient */
    protected $client;

    public function __construct(AuthorizationChallenge $httpChallenge, int $tries = null, int $retryAfter = null, $retryList = [])
    {
        $this->challenge = $httpChallenge;
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
    }

    /**
     * Cleans up the HTTP challenge by removing the file. Should be called right after the challenge was approved.
     * @return void
     */
    public function handle()
    {
        $generator = PathGeneratorFactory::create();
        Storage::disk(config('lets_encrypt.challenge_disk'))->delete($generator->getChallengePath($this->challenge->getToken()));
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        event(new CleanUpChallengeFailed($this));
    }
}
