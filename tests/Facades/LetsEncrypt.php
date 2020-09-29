<?php

namespace Daanra\LaravelLetsEncrypt\Tests\Facades;

use Daanra\LaravelLetsEncrypt\Jobs\RegisterAccount;
use Daanra\LaravelLetsEncrypt\Tests\TestCase;
use Illuminate\Support\Facades\Bus;

class LetsEncrypt extends TestCase
{
    /** @test */
    public function test_can_create()
    {
        Bus::fake();

        \Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt::create('somedomain.com');

        Bus::assertDispatched(RegisterAccount::class);
    }
}
