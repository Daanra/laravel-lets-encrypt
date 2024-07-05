<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Events\BeforeDnsChallengeAuthorization;
use Daanra\LaravelLetsEncrypt\Events\RequestAuthorizationFailed;
use Daanra\LaravelLetsEncrypt\Exceptions\FailedToMoveChallengeException;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Daanra\LaravelLetsEncrypt\Traits\Retryable;
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
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, Retryable;

    /** @var LetsEncryptCertificate */
    protected $certificate;

    /** @var bool */
    protected $sync;


    public function __construct(LetsEncryptCertificate $certificate, int $tries = null, int $retryAfter = null, $retryList = [])
    {
        $this->sync = false;
        $this->certificate = $certificate;
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
    }

    /**
     * Out of the array of challenges we have, we want to find the HTTP challenge, because that's the
     * easiest one to solve in this scenario.
     * @param AuthorizationChallenge[] $challenges
     * @return AuthorizationChallenge
     */
    protected function getHttpChallenge(array $challenges): AuthorizationChallenge
    {
        return collect($challenges)->first(function (AuthorizationChallenge $challenge): bool {
            return Str::startsWith($challenge->getType(), 'http');
        });
    }

    /**
     * Out of the array of challenges we have, we want to find the DNS challenge.
     * @param AuthorizationChallenge[] $challenges
     * @return AuthorizationChallenge
     */
    protected function getDnsChallenge(array $challenges): AuthorizationChallenge
    {
        return collect($challenges)->first(function (AuthorizationChallenge $challenge): bool {
            return Str::startsWith($challenge->getType(), 'dns');
        });
    }

    /**
     * Stores the HTTP-01 challenge at the appropriate place on disk.
     * @param AuthorizationChallenge $challenge
     * @throws FailedToMoveChallengeException
     */
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
        $challenges = $client->requestAuthorization($this->certificate->domain);
        $defaultChallenge = config('lets_encrypt.challenge_type', 'http');

        switch ($defaultChallenge) {
            case 'http':
                $challenge = $this->getHttpChallenge($challenges);
                $this->placeChallenge($challenge);
                break;
            case 'dns':
                $challenge = $this->getDnsChallenge($challenges);
                $payLoad = $challenge->getPayload();
                event(new BeforeDnsChallengeAuthorization($this->certificate, $challenge, $payLoad));
                break;
            default:
                throw new \RuntimeException('Invalid challenge type: ' . $defaultChallenge);
        }

        if ($this->sync) {
            ChallengeAuthorization::dispatchSync($challenge, $this->tries, $this->retryAfter, $this->retryList);
        } else {
            ChallengeAuthorization::dispatch($challenge, $this->tries, $this->retryAfter, $this->retryList);
        }
    }

    protected function setSync(bool $sync)
    {
        $this->sync = $sync;
    }

    public static function dispatchNow(LetsEncryptCertificate $certificate)
    {
        $job = new static($certificate);
        $job->setSync(true);
        app(Dispatcher::class)->dispatchSync($job);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        event(new RequestAuthorizationFailed($exception, $this->certificate));
    }
}
