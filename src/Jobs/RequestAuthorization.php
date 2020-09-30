<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Exceptions\FailedToMoveChallengeException;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequestAuthorization implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /** @var string */
    protected $domain;

    /** @var bool */
    protected $sync;

    public function __construct(string $domain)
    {
        $this->sync = false;
        $this->domain = $domain;
    }

    protected function getHttpChallenge(array $challenges): AuthorizationChallenge
    {
        return collect($challenges)->first(function (AuthorizationChallenge $challenge): bool {
            return Str::startsWith($challenge->getType(), 'http');
        });
    }

    protected function placeChallenge(AuthorizationChallenge $challenge): void
    {
        $path = PathGeneratorFactory::create()->getChallengePath($challenge->getToken());
        $success = Storage::disk(config('lets_encrypt.challenge_disk'))->put($path, $challenge->getPayload());

        if ($success === false) {
            throw new FailedToMoveChallengeException($path);
        }
    }

    public function handle()
    {
        $client = LetsEncrypt::createClient();
        $challenges = $client->requestAuthorization($this->domain);
        $httpChallenge = $this->getHttpChallenge($challenges);
        $this->placeChallenge($httpChallenge);
        if ($this->sync) {
            ChallengeAuthorization::dispatchNow($httpChallenge);
        } else {
            ChallengeAuthorization::dispatch($httpChallenge);
        }
    }

    protected function setSync(bool $sync)
    {
        $this->sync = $sync;
    }

    public static function dispatchNow(string $domain)
    {
        $job = new static($domain);
        $job->setSync(true);
        app(Dispatcher::class)->dispatchNow($job);
    }
}
