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
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__.'/../database/migrations/create_lets_encrypt_certificates_table.php.stub';
        (new \CreateLetsEncryptCertificatesTable())->up();
    }
}
