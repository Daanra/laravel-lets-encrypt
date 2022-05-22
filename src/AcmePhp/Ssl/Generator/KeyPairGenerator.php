<?php

/*
 * This file is part of the Acme PHP project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator;

use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Exception\KeyGenerationException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Exception\KeyPairGenerationException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\DhKey\DhKeyGenerator;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\DsaKey\DsaKeyGenerator;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\EcKey\EcKeyGenerator;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\RsaKey\RsaKeyGenerator;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\RsaKey\RsaKeyOption;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\KeyPair;
use Webmozart\Assert\Assert;

/**
 * Generate random KeyPair using OpenSSL.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class KeyPairGenerator
{
    private $generator;

    public function __construct(PrivateKeyGeneratorInterface $generator = null)
    {
        $this->generator = $generator ?: new ChainPrivateKeyGenerator(
            [
                new RsaKeyGenerator(),
                new EcKeyGenerator(),
                new DhKeyGenerator(),
                new DsaKeyGenerator(),
            ]
        );
    }

    /**
     * Generate KeyPair.
     *
     * @param KeyOption $keyOption configuration of the key to generate
     *
     * @throws KeyPairGenerationException when OpenSSL failed to generate keys
     *
     * @return KeyPair
     */
    public function generateKeyPair($keyOption = null)
    {
        if (null === $keyOption) {
            $keyOption = new RsaKeyOption();
        }
        if (\is_int($keyOption)) {
            @trigger_error('Passing a keySize to "generateKeyPair" is deprecated since version 1.1 and will be removed in 2.0. Pass an instance of KeyOption instead', E_USER_DEPRECATED);
            $keyOption = new RsaKeyOption($keyOption);
        }
        Assert::isInstanceOf($keyOption, KeyOption::class);

        try {
            $privateKey = $this->generator->generatePrivateKey($keyOption);
        } catch (KeyGenerationException $e) {
            throw new KeyPairGenerationException('Fail to generate a KeyPair with the given options', 0, $e);
        }

        return new KeyPair(
            $privateKey->getPublicKey(),
            $privateKey
        );
    }
}
