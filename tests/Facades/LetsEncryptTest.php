<?php

namespace Daanra\LaravelLetsEncrypt\Tests\Facades;

use Daanra\LaravelLetsEncrypt\Jobs\RegisterAccount;
use Daanra\LaravelLetsEncrypt\Jobs\RequestAuthorization;
use Daanra\LaravelLetsEncrypt\Jobs\RequestCertificate;
use Daanra\LaravelLetsEncrypt\Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

class LetsEncryptTest extends TestCase
{
    /** @test */
    public function test_can_create_now()
    {
        Bus::fake();

        \Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt::createNow('somedomain.com');

        Bus::assertDispatched(RegisterAccount::class);
    }

    /** @test */
    public function test_can_create()
    {
        Queue::fake();

        \Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt::create('someotherdomain.com');

        Queue::assertPushedWithChain(RegisterAccount::class, [
            RequestAuthorization::class,
            RequestCertificate::class,
        ]);
    }
}
