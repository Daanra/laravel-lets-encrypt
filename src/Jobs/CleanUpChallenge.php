<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
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

    public function __construct(AuthorizationChallenge $httpChallenge)
    {
        $this->challenge = $httpChallenge;
    }

    public function handle()
    {
        $generator = PathGeneratorFactory::create();
        Storage::disk(config('lets_encrypt.challenge_disk'))->delete($generator->getChallengePath($this->challenge->getToken()));
    }
}
