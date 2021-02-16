<?php

namespace Daanra\LaravelLetsEncrypt\Tests\Encoders;

use Daanra\LaravelLetsEncrypt\Encoders\PemEncoder;
use Daanra\LaravelLetsEncrypt\Tests\TestCase;

class PemEncoderTest extends TestCase
{
    /** @test */
    public function test_encoder()
    {
        $this->assertEquals("test    test\n", PemEncoder::encode('  test    test  '));
        $this->assertEquals("2test1\n", PemEncoder::encode('2test1'));
    }
}
