<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
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

    public function __construct(string $email = null)
    {
        $this->email = $email;
    }

    public function handle()
    {
        $client = LetsEncrypt::createClient();
        $client->registerAccount(null, $this->email);
    }
}
