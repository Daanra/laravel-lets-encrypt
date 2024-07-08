<?php

namespace Daanra\LaravelLetsEncrypt\Events;

use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Interfaces\LetsEncryptCertificateFailed;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeAuthorizationFailed implements LetsEncryptCertificateFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \Throwable */
    protected $exception;

    /** @var mixed */
    protected $challenge;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\Throwable $exception, AuthorizationChallenge $challenge)
    {
        $this->challenge = $challenge;
        $this->exception = $exception;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
