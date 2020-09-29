<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Ssl\CertificateRequest;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\Generator\KeyPairGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RequestCertificate implements ShouldQueue
{
    use Dispatchable;

    /** @var string */
    protected $domain;

    /** @var AcmeClient */
    protected $client;

    public function __construct(AcmeClient $client, string $domain)
    {
        $this->client = $client;
        $this->domain = $domain;
    }

    public function handle()
    {
        $distinguishedName = new DistinguishedName($this->domain);
        $csr = new CertificateRequest($distinguishedName, (new KeyPairGenerator())->generateKeyPair());
        $this->client->requestCertificate($this->domain, $csr);
    }
}
