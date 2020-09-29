<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class CleanUpChallenge implements ShouldQueue
{
    use Dispatchable;

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
        Storage::delete($generator->getPath($this->challenge->getToken()));
    }
}
