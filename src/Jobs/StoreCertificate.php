<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Ssl\Certificate;
use AcmePhp\Ssl\PrivateKey;
use Daanra\LaravelLetsEncrypt\Contracts\PathGenerator;
use Daanra\LaravelLetsEncrypt\Encoders\PemEncoder;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class StoreCertificate implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /** @var Certificate */
    protected $certificate;

    /** @var string */
    protected $domain;

    protected $privateKey;

    public function __construct(string $domain, Certificate $certificate, PrivateKey $privateKey)
    {
        $this->domain = $domain;
        $this->certificate = $certificate;
        $this->privateKey = $privateKey;
    }

    /**
     * Stores four files on disk: 'fullchain.pem', 'chain.pem', 'cert.pem' and 'privkey.pem'
     */
    public function handle()
    {
        $certPem = PemEncoder::encode($this->certificate->getPEM());
        $chainPem = collect($this->certificate->getIssuerChain())
            ->reduce(function (string $carry, Certificate $certificate): string {
                return $carry . PemEncoder::encode($certificate->getPEM());
            }, '');

        $fullChainPem = $certPem . $chainPem;

        $privkeyPem = PemEncoder::encode($this->privateKey->getPEM());

        $factory = PathGeneratorFactory::create();

        $this->storeInPossiblyNonExistingDirectory($factory, 'cert.pem', $certPem);
        $this->storeInPossiblyNonExistingDirectory($factory, 'chain.pem', $certPem);
        $this->storeInPossiblyNonExistingDirectory($factory, 'fullchain.pem', $fullChainPem);
        $this->storeInPossiblyNonExistingDirectory($factory, 'privkey.pem', $privkeyPem);
    }

    /**
     * Creates the directory if it does not exist yet to prevent an error.
     * @param PathGenerator $generator
     * @param string $filename
     * @param string $contents
     */
    protected function storeInPossiblyNonExistingDirectory(PathGenerator $generator, string $filename, string $contents): void
    {
        $path = $generator->getCertificatePath($this->domain, $filename);
        $directory = File::dirname($path);
        File::ensureDirectoryExists($directory);
        File::put($path, $contents);
    }
}
