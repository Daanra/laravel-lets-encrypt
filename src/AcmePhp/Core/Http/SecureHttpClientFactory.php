<?php

/*
 * This file is part of the Acme PHP project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daanra\LaravelLetsEncrypt\AcmePhp\Core\Http;

use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\KeyPair;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Parser\KeyParser;
use Daanra\LaravelLetsEncrypt\AcmePhp\Ssl\Signer\DataSigner;
use GuzzleHttp\ClientInterface;

/**
 * Guzzle HTTP client wrapper to send requests signed with the account KeyPair.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class SecureHttpClientFactory
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var Base64SafeEncoder
     */
    private $base64Encoder;

    /**
     * @var KeyParser
     */
    private $keyParser;

    /**
     * @var DataSigner
     */
    private $dataSigner;

    /**
     * @var ServerErrorHandler
     */
    private $errorHandler;

    public function __construct(
        ClientInterface $httpClient,
        Base64SafeEncoder $base64Encoder,
        KeyParser $keyParser,
        DataSigner $dataSigner,
        ServerErrorHandler $errorHandler
    ) {
        $this->httpClient = $httpClient;
        $this->base64Encoder = $base64Encoder;
        $this->keyParser = $keyParser;
        $this->dataSigner = $dataSigner;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Create a SecureHttpClient using a given account KeyPair.
     *
     * @return SecureHttpClient
     */
    public function createSecureHttpClient(KeyPair $accountKeyPair)
    {
        return new SecureHttpClient(
            $accountKeyPair,
            $this->httpClient,
            $this->base64Encoder,
            $this->keyParser,
            $this->dataSigner,
            $this->errorHandler
        );
    }
}
