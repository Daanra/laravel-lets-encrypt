<?php

namespace Daanra\LaravelLetsEncrypt\Tests\Models;

use Daanra\LaravelLetsEncrypt\Builders\LetsEncryptCertificateBuilder;
use Daanra\LaravelLetsEncrypt\Collections\LetsEncryptCertificateCollection;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Jobs\RegisterAccount;
use Daanra\LaravelLetsEncrypt\Jobs\RequestAuthorization;
use Daanra\LaravelLetsEncrypt\Jobs\RequestCertificate;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Daanra\LaravelLetsEncrypt\Tests\TestCase;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Queue;

class LetsEncryptCertificateTest extends TestCase
{
    /** @test */
    public function test_correct_builder_instance()
    {
        $builder = LetsEncryptCertificate::query();

        $this->assertInstanceOf(LetsEncryptCertificateBuilder::class, $builder);
    }

    /** @test */
    public function test_correct_collection_instance()
    {
        $collection = LetsEncryptCertificate::all();

        $this->assertInstanceOf(LetsEncryptCertificateCollection::class, $collection);
    }

    /** @test */
    public function test_has_expired_attribute()
    {
        $certificate = LetsEncryptCertificate::create([
            'domain' => 'test1.test',
        ]);

        $this->assertFalse(
            $certificate->has_expired,
            'Certificate has not been issued so it should not be marked as expired.'
        );

        $certificate->update([
            'last_renewed_at' => now(),
            'created' => true,
        ]);

        $this->assertFalse($certificate->has_expired, 'Certificate has just been issued so should not be considered as expired.');

        $certificate->update([
            'last_renewed_at' => now()->subMonths(4),
        ]);

        $this->assertTrue($certificate->has_expired, 'Certificate has been issued 4 months ago so should be marked as expired.');
    }

    /** @test */
    public function test_renew()
    {
        $certificate = LetsEncryptCertificate::create([
            'domain' => 'test2.test',
        ]);

        Queue::fake();

        $pendingDispatch = $certificate->renew();

        $this->assertInstanceOf(PendingDispatch::class, $pendingDispatch);

        // Jobs are only pushed after the pending dispatch leaves memory.
        $pendingDispatch->__destruct();

        Queue::assertPushedWithChain(RegisterAccount::class, [
            new RequestAuthorization($certificate),
            new RequestCertificate($certificate),
        ]);
    }

    /** @test */
    public function test_renew_now()
    {
        $certificate = LetsEncryptCertificate::create([
            'domain' => 'test3.test',
        ]);

        Queue::fake();

        LetsEncrypt::shouldReceive('renewNow')
            ->once()
            ->with($certificate)
            ->andReturn($certificate);

        $instance = $certificate->renewNow();

        Queue::assertNothingPushed();

        $this->assertEquals($certificate, $instance);
    }
}
