<?php

namespace Daanra\LaravelLetsEncrypt\Events;

use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Protocol\AuthorizationChallenge;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeforeDnsChallengeAuthorization
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var LetsEncryptCertificate */
    public $certificate;

    /** @var AuthorizationChallenge */
    public $challenge;

    /** @var string */
    public $payLoad;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LetsEncryptCertificate $certificate,  AuthorizationChallenge $challenge, string $payLoad)
    {
        $this->certificate = $certificate;
        $this->challenge = $challenge;
        $this->payLoad = $payLoad;
    }
}