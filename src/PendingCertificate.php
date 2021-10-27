<?php

namespace Daanra\LaravelLetsEncrypt;

use Daanra\LaravelLetsEncrypt\Exceptions\DomainAlreadyExists;
use Daanra\LaravelLetsEncrypt\Exceptions\InvalidDomainException;
use Daanra\LaravelLetsEncrypt\Jobs\RegisterAccount;
use Daanra\LaravelLetsEncrypt\Jobs\RequestAuthorization;
use Daanra\LaravelLetsEncrypt\Jobs\RequestCertificate;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;

class PendingCertificate
{

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var array
     */
    protected $chain = [];

    /**
     * @var int
     */
    protected $tries;

    /**
     * @var int
     */
    protected $retryAfter;

    /**
     * @var array
     */
    protected $retryList = [];

    /**
     * @var \DateTimeInterface|\DateInterval|int|null
     */
    protected $delay = 0;

    /**
     * PendingCertificate constructor.
     * @param string $domain
     */
    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Creates a new certificate. The heavy work is pushed on the queue.
     * @return LetsEncryptCertificate
     * @throws DomainAlreadyExists
     * @throws InvalidDomainException
     */
    public function create(): LetsEncryptCertificate
    {
        LetsEncrypt::validateDomain($this->domain);
        LetsEncrypt::checkDomainDoesNotExist($this->domain);

        $email = config('lets_encrypt.universal_email_address');

        $certificate = LetsEncryptCertificate::create([
            'domain' => $this->domain,
        ]);

        RegisterAccount::withChain(array_merge([
                new RequestAuthorization(
                    $certificate,
                    $this->tries,
                    $this->retryAfter,
                    $this->retryList
                ),
                new RequestCertificate(
                    $certificate,
                    $this->tries,
                    $this->retryAfter,
                    $this->retryList
                ),
            ], $this->chain))
                ->dispatch($email, $this->tries, $this->retryAfter, $this->retryList)
                ->delay($this->delay);

        return $certificate;
    }

    /**
     * @return LetsEncryptCertificate
     * @throws InvalidDomainException
     */
    public function renew(): LetsEncryptCertificate
    {
        $certificate = LetsEncryptCertificate::where('domain', $this->domain)->first();
        $email = config('lets_encrypt.universal_email_address', null);

        RegisterAccount::withChain(array_merge([
            new RequestAuthorization(
                $certificate,
                $this->tries,
                $this->retryAfter,
                $this->retryList
            ),
            new RequestCertificate(
                $certificate,
                $this->tries,
                $this->retryAfter,
                $this->retryList
            ),
        ], $this->chain))
            ->dispatch($email, $this->tries, $this->retryAfter, $this->retryList)
            ->delay($this->delay);

        return $certificate;
    }

    /**
     * @param int $tries
     * @return static
     */
    public function setTries(int $tries): self
    {
        $this->tries = $tries;

        return $this;
    }

    /**
     * @param int $retryAfter
     * @return static
     */
    public function retryAfter(int $retryAfter): self
    {
        $this->retryAfter = $retryAfter;

        return $this;
    }

    /**
     * @param array $chain
     * @return static
     */
    public function chain(array $chain): self
    {
        $this->chain = $chain;

        return $this;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @param array $retryList
     * @return static
     */
    public function setRetryList(array $retryList): self
    {
        $this->retryList = $retryList;

        return $this;
    }

    /**
     * @param \DateTimeInterface|\DateInterval|int|null $delay
     * @return static
     */
    public function delay($delay): self
    {
        $this->delay = $delay;

        return $this;
    }
}
