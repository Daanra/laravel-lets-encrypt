<?php

namespace Daanra\LaravelLetsEncrypt\Tests\Builders;

use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\Tests\TestCase;

class LetsEncryptCertificateBuilderTest extends TestCase
{
    /** @test */
    public function test_expired()
    {
        $expired = LetsEncryptCertificate::create([
            'domain' => 'test.com',
            'last_renewed_at' => now()->subYear(),
        ]);

        $nonExisting = LetsEncryptCertificate::create([
            'domain' => 'test.com',
        ]);

        $active = LetsEncryptCertificate::create([
            'domain' => 'test.com',
            'last_renewed_at' => now()->subDays(2),
        ]);

        $certificates = LetsEncryptCertificate::query()
            ->expired()
            ->get();

        $this->assertEquals(1, $certificates->count());
        $this->assertEquals($expired->id, $certificates->first()->id);
    }
}
