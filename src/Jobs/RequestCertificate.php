<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Ssl\CertificateRequest;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\Generator\KeyPairGenerator;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequestCertificate implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /** @var string */
    protected $domain;


    public function __construct(string $domain)
    {
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
        StoreCertificate::dispatch($this->domain, $certificate, $privateKey);
    }
}
