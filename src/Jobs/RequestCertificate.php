<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Ssl\CertificateRequest;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\Generator\KeyPairGenerator;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\Events\RequestCertificateFailed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequestCertificate implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /** @var LetsEncryptCertificate */
    protected $certificate;

    /** @var bool */
    protected $sync;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter;

    /**
     * The list of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $retryList;


    public function __construct(LetsEncryptCertificate $certificate, int $tries = null, int $retryAfter = null, $retryList = [])
    {
        $this->sync = false;
        $this->certificate = $certificate;
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
    }

    public function handle()
    {
        $distinguishedName = new DistinguishedName($this->certificate->domain);
        $csr = new CertificateRequest($distinguishedName, (new KeyPairGenerator())->generateKeyPair());
        $client = LetsEncrypt::createClient();
        $certificateResponse = $client->requestCertificate($this->certificate->domain, $csr);
        $certificate = $certificateResponse->getCertificate();
        $privateKey = $csr->getKeyPair()->getPrivateKey();

        if ($this->sync) {
            StoreCertificate::dispatchNow($this->certificate, $certificate, $privateKey, $this->tries, $this->retryAfter, $this->retryList);
        } else {
            StoreCertificate::dispatch($this->certificate, $certificate, $privateKey, $this->tries, $this->retryAfter, $this->retryList);
        }
    }

    protected function setSync(bool $sync)
    {
        $this->sync = $sync;
    }

    public static function dispatchNow(LetsEncryptCertificate $certificate)
    {
        $job = new static($certificate);
        $job->setSync(true);
        app(Dispatcher::class)->dispatchNow($job);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function retryAfter()
    {
        return (!empty($this->retryList)) ? $this->retryList[$this->attempts() - 1] : 0;
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        event(new RequestCertificateFailed($this));
    }
}
