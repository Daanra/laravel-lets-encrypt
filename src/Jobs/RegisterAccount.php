<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RegisterAccount implements ShouldQueue
{
    use Dispatchable;

    /** @var string */
    protected $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function handle()
    {
        $client = LetsEncrypt::createClient();
        $client->registerAccount(null, 'daanraatjes@gmail.com');
    }
}
