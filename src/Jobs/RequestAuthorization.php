<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Exceptions\FailedToMoveChallengeException;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Daanra\LaravelLetsEncrypt\Events\RequestAuthorizationFailed;
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

    /** @var LetsEncryptCertificate */
    protected $certificate;

    /** @var bool */
    protected $sync;

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
        $httpChallenge = $this->getHttpChallenge($challenges);
        $this->placeChallenge($httpChallenge);

        if ($this->sync) {
            ChallengeAuthorization::dispatchNow($httpChallenge, $this->tries, $this->retryAfter, $this->retryList);
        } else {
            ChallengeAuthorization::dispatch($httpChallenge, $this->tries, $this->retryAfter, $this->retryList);
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
        app(Dispatcher::class)->dispatchNow($job);
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
        event(new RequestAuthorizationFailed($this));
    }
}
