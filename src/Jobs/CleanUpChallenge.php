<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Events\CleanUpChallengeFailed;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanUpChallenge implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /** @var AuthorizationChallenge */
    protected $challenge;

    /** @var AcmeClient */
    protected $client;

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
     * Cleans up the HTTP challenge by removing the file. Should be called right after the challenge was approved.
     * @return void
     */
    public function handle()
    {
        $generator = PathGeneratorFactory::create();
        Storage::disk(config('lets_encrypt.challenge_disk'))->delete($generator->getChallengePath($this->challenge->getToken()));
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
        event(new CleanUpChallengeFailed($this));
    }
}
