<?php

namespace Daanra\LaravelLetsEncrypt\Events;

use Daanra\LaravelLetsEncrypt\Interfaces\LetsEncryptCertificateFailed;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestCertificateFailed implements LetsEncryptCertificateFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \Throwable */
    protected $exception;

    /** @var LetsEncryptCertificate */
    protected $certificate;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\Throwable $exception, LetsEncryptCertificate $certificate)
    {
        $this->exception = $exception;
        $this->certificate = $certificate;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getCertificate(): LetsEncryptCertificate
    {
        return $this->certificate;
    }
}
