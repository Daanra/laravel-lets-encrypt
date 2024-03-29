<?php

namespace Daanra\LaravelLetsEncrypt\Tests;

use Daanra\LaravelLetsEncrypt\LetsEncryptServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

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
        include_once __DIR__.'/../database/migrations/add_lets_encrypt_certificates_subject_alternative_names.php.stub';
        (new \CreateLetsEncryptCertificatesTable())->up();
        (new \AddLetsEncryptCertificatesSubjectAlternativeNames())->up();
    }
}
