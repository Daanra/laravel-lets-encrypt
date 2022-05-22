<?php

/*
 * This file is part of the Acme PHP project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\DsaKey;

use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\KeyOption;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\OpensslPrivateKeyGeneratorTrait;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Generator\PrivateKeyGeneratorInterface;
use Webmozart\Assert\Assert;

/**
 * Generate random DSA private key using OpenSSL.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class DsaKeyGenerator implements PrivateKeyGeneratorInterface
{
    use OpensslPrivateKeyGeneratorTrait;

    /**
     * @param DsaKeyOption|KeyOption $keyOption
     */
    public function generatePrivateKey(KeyOption $keyOption)
    {
        Assert::isInstanceOf($keyOption, DsaKeyOption::class);

        return $this->generatePrivateKeyFromOpensslOptions(
            [
                'private_key_type' => OPENSSL_KEYTYPE_DSA,
                'private_key_bits' => $keyOption->getBits(),
            ]
        );
    }

    public function supportsKeyOption(KeyOption $keyOption)
    {
        return $keyOption instanceof DsaKeyOption;
    }
}
