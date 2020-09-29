<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Ssl\CertificateRequest;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\Generator\KeyPairGenerator;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RequestCertificate implements ShouldQueue
{
    use Dispatchable;

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
        $client->requestCertificate($this->domain, $csr);
    }
}
