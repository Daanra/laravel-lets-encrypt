<?php

namespace Daanra\LaravelLetsEncrypt;

use Daanra\LaravelLetsEncrypt\Exceptions\DomainAlreadyExists;
use Daanra\LaravelLetsEncrypt\Exceptions\InvalidDomainException;
use Daanra\LaravelLetsEncrypt\Jobs\RegisterAccount;
use Daanra\LaravelLetsEncrypt\Jobs\RequestAuthorization;
use Daanra\LaravelLetsEncrypt\Jobs\RequestCertificate;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Illuminate\Foundation\Bus\PendingDispatch;

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
     * @var int
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
     * @return array{LetsEncryptCertificate, PendingDispatch}
     * @throws DomainAlreadyExists
     * @throws InvalidDomainException
     */
    public function create(): array
    {
        LetsEncrypt::validateDomain($this->domain);
        LetsEncrypt::checkDomainDoesNotExist($this->domain);

        $email = config('lets_encrypt.universal_email_address');

        $certificate = LetsEncryptCertificate::create([
            'domain' => $this->domain,
        ]);

        return [
            $certificate, RegisterAccount::withChain(array_merge([
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
                ->delay($this->delay),
        ];
    }

    /**
     * @return mixed
     * @throws InvalidDomainException
     */
    public function renew()
    {
        $domain = LetsEncryptCertificate::where('domain', $this->domain)->first();
        $email = config('lets_encrypt.universal_email_address', null);

        return RegisterAccount::withChain(array_merge([
            new RequestAuthorization(
                $domain,
                $this->tries,
                $this->retryAfter,
                $this->retryList
            ),
            new RequestCertificate(
                $domain,
                $this->tries,
                $this->retryAfter,
                $this->retryList
            ),
        ], $this->chain))
            ->dispatch($email, $this->tries, $this->retryAfter, $this->retryList)
            ->delay($this->delay);
    }

    /**
     * @param int $tries
     * @return $this
     */
    public function setTries(int $tries): PendingCertificate
    {
        $this->tries = $tries;

        return $this;
    }

    /**
     * @param int $retryAfter
     * @return $this
     */
    public function setRetryAfter(int $retryAfter): PendingCertificate
    {
        $this->retryAfter = $retryAfter;

        return $this;
    }

    /**
     * @param array $chain
     * @return $this
     */
    public function setChain(array $chain): PendingCertificate
    {
        $this->chain = $chain;

        return $this;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @param array $retryList
     * @return $this
     */
    public function setRetryList(array $retryList): PendingCertificate
    {
        $this->retryList = $retryList;

        return $this;
    }

    /**
     * @param \DateTimeInterface|\DateInterval|int|null $delay
     * @return $this
     */
    public function setDelay($delay): PendingCertificate
    {
        $this->delay = $delay;

        return $this;
    }
}
