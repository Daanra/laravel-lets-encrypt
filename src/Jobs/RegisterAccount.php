<?php

namespace Daanra\LaravelLetsEncrypt\Jobs;

use AcmePhp\Core\AcmeClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RegisterAccount implements ShouldQueue
{
    use Dispatchable;

    /** @var string */
    protected $email;

    /** @var AcmeClient */
    protected $client;

    public function __construct(AcmeClient $client, string $email)
    {
        $this->client = $client;
        $this->email = $email;
    }

    public function handle()
    {
        $this->client->registerAccount(null, 'daanraatjes@gmail.com');
    }
}
