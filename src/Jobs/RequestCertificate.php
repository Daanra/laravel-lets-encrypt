<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Ssl\CertificateRequest;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\Generator\KeyPairGenerator;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequestCertificate implements ShouldQueue
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

    public function handle()
    {
        $distinguishedName = new DistinguishedName($this->domain);
        $csr = new CertificateRequest($distinguishedName, (new KeyPairGenerator())->generateKeyPair());
        $client = LetsEncrypt::createClient();
        $certificateResponse = $client->requestCertificate($this->domain, $csr);
        $certificate = $certificateResponse->getCertificate();
        $privateKey = $csr->getKeyPair()->getPrivateKey();
        if ($this->sync) {
            StoreCertificate::dispatchNow($this->domain, $certificate, $privateKey);
        } else {
            StoreCertificate::dispatch($this->domain, $certificate, $privateKey);
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
