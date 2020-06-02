<?php

namespace Spatie\Skeleton\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Daanra\LaravelLetsEncrypt\LetsEncryptServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LetsEncryptServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {

    }
}
