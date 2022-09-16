<?php

namespace Daanra\LaravelLetsEncrypt\Tests\Facades;

use Daanra\LaravelLetsEncrypt\Exceptions\InvalidDomainException;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Daanra\LaravelLetsEncrypt\Jobs\RegisterAccount;
use Daanra\LaravelLetsEncrypt\Jobs\RequestAuthorization;
use Daanra\LaravelLetsEncrypt\Jobs\RequestCertificate;
use Daanra\LaravelLetsEncrypt\PendingCertificate;
use Daanra\LaravelLetsEncrypt\Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

class LetsEncryptTest extends TestCase
{
    /** @test */
    public function test_can_create_now()
    {
        Bus::fake();

        $certificate = LetsEncrypt::createNow('somedomain.com');
        $this->assertEquals('somedomain.com', $certificate->domain);

        Bus::assertDispatched(RegisterAccount::class);
    }

    /** @test */
    public function test_can_create()
    {
        Queue::fake();

        [$certificate] = LetsEncrypt::create('someotherdomain.com');

        $this->assertEquals('someotherdomain.com', $certificate->domain);

        Queue::assertPushedWithChain(RegisterAccount::class, [
            RequestAuthorization::class,
            RequestCertificate::class,
        ]);
    }

    public function test_invalid_domain()
    {
        $this->expectException(InvalidDomainException::class);
        LetsEncrypt::validateDomain('https://mydomain.com');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_is_valid_domain()
    {
        LetsEncrypt::validateDomain('test-some-domain.company');
        LetsEncrypt::validateDomain('google.com');
        LetsEncrypt::validateDomain('test.test.test.dev');
    }

    public function test_pending_certificate()
    {
        $pending = LetsEncrypt::certificate('test.dev');
        $this->assertInstanceOf(PendingCertificate::class, $pending);
    }

    public function test_can_create_pending()
    {
        Queue::fake();

        $certificate = LetsEncrypt::certificate('test.test')->create();

        $this->assertEquals('test.test', $certificate->domain);
        $this->assertEquals([], $certificate->subject_alternative_names);

        Queue::assertPushedWithChain(RegisterAccount::class, [
            RequestAuthorization::class,
            RequestCertificate::class,
        ]);
    }

    /** @test */
    public function test_can_create_now_with_san()
    {
        Bus::fake();

        $certificate = LetsEncrypt::certificate('somedomain.com')
            ->setSubjectAlternativeNames(['other.somedomain.com'])
            ->create();

        $this->assertEquals('somedomain.com', $certificate->domain);
        $this->assertEquals(['other.somedomain.com'], $certificate->subject_alternative_names);

        Bus::assertDispatched(RegisterAccount::class);
    }
}
