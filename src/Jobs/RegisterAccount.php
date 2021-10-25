<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use Daanra\LaravelLetsEncrypt\Events\RegisterAccountFailed;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Traits\JobTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegisterAccount implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, JobTrait;

    /** @var string|null */
    protected $email;

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
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        event(new RegisterAccountFailed($this));
    }
}
