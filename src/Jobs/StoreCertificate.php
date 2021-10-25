<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Ssl\Certificate;
use AcmePhp\Ssl\PrivateKey;
use Daanra\LaravelLetsEncrypt\Contracts\PathGenerator;
use Daanra\LaravelLetsEncrypt\Encoders\PemEncoder;
use Daanra\LaravelLetsEncrypt\Events\StoreCertificateFailed;
use Daanra\LaravelLetsEncrypt\Exceptions\FailedToStoreCertificate;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\Support\PathGeneratorFactory;
use Daanra\LaravelLetsEncrypt\Traits\JobTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StoreCertificate implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, JobTrait;

    /**
     * @var Certificate
     */
    protected $certificate;

    /**
     * @var LetsEncryptCertificate
     */
    protected $dbCertificate;

    /**
     * @var PrivateKey
     */
    protected $privateKey;


    public function __construct(LetsEncryptCertificate $dbCertificate, Certificate $certificate, PrivateKey $privateKey, int $tries = null, int $retryAfter = null, $retryList = [])
    {
        $this->dbCertificate = $dbCertificate;
        $this->certificate = $certificate;
        $this->privateKey = $privateKey;
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
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

        $this->storeInPossiblyNonExistingDirectory($factory, 'cert', $certPem);
        $this->storeInPossiblyNonExistingDirectory($factory, 'chain', $certPem);
        $this->storeInPossiblyNonExistingDirectory($factory, 'fullchain', $fullChainPem);
        $this->storeInPossiblyNonExistingDirectory($factory, 'privkey', $privkeyPem);
        $this->dbCertificate->last_renewed_at = now();
        $this->dbCertificate->created = true;
        $this->dbCertificate->save();
    }

    /**
     * Creates the directory if it does not exist yet to prevent an error.
     * @param PathGenerator $generator
     * @param string $filename
     * @param string $contents
     * @throws FailedToStoreCertificate
     */
    protected function storeInPossiblyNonExistingDirectory(PathGenerator $generator, string $filename, string $contents): void
    {
        $path = $generator->getCertificatePath($this->dbCertificate->domain, $filename . '.pem');
        $directory = File::dirname($path);
        $fs = Storage::disk(config('lets_encrypt.certificate_disk'));
        if (! $fs->exists($directory)) {
            $fs->makeDirectory($directory);
        }

        $this->dbCertificate[$filename . '_path'] = $path;

        if ($fs->put($path, $contents) === false) {
            throw new FailedToStoreCertificate($path);
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        event(new StoreCertificateFailed($this));
    }
}
