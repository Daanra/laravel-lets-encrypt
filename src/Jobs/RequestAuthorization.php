<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Exceptions\FailedToMoveChallengeException;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Illuminate\Bus\Queueable;
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

    /** @var AcmeClient */
    protected $client;

    public function __construct(AcmeClient $client, string $domain)
    {
        $this->client = $client;
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
        $path = PathGeneratorFactory::create()->getPath($challenge->getToken());
        $success = Storage::put($path, $challenge->getPayload());

        if ($success === false) {
            throw new FailedToMoveChallengeException($path);
        }
    }


    public function handle()
    {
        $challenges = $this->client->requestAuthorization($this->domain);
        $httpChallenge = $this->getHttpChallenge($challenges);
        $this->placeChallenge($httpChallenge);
        ChallengeAuthorization::dispatch($httpChallenge);
    }
}
