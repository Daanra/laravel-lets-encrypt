<?php

namespace Daanra\LaravelLetsEncrypt\Events;

use Daanra\LaravelLetsEncrypt\Interfaces\LetsEncryptCertificateFailed;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CleanUpChallengeFailed implements LetsEncryptCertificateFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \Throwable */
    protected $exception;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
