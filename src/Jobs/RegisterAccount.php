<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Events\RegisterAccountFailed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegisterAccount implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /** @var string|null */
    protected $email;

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


    public function __construct(string $email = null, int $tries = null, int $retryAfter = null, $retryList = [])
    {
        $this->email = $email;
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
    }

    public function handle()
    {
        $client = LetsEncrypt::createClient();
        $client->registerAccount(null, $this->email);
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
        event(new RegisterAccountFailed($this));
    }
}
