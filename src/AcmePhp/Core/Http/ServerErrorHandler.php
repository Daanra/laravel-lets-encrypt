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

use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\AcmeCoreServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\BadCsrServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\BadNonceServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\CaaServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\ConnectionServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\DnsServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\IncorrectResponseServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\InternalServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\InvalidContactServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\InvalidEmailServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\MalformedServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\OrderNotReadyServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\RateLimitedServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\RejectedIdentifierServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\TlsServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\UnauthorizedServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\UnknownHostServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\UnsupportedContactServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\UnsupportedIdentifierServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server\UserActionRequiredServerException;
use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Util\JsonDecoder;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Create appropriate exception for given server response.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class ServerErrorHandler
{
    private static $exceptions = [
        'badCSR' => BadCsrServerException::class,
        'badNonce' => BadNonceServerException::class,
        'caa' => CaaServerException::class,
        'connection' => ConnectionServerException::class,
        'dns' => DnsServerException::class,
        'incorrectResponse' => IncorrectResponseServerException::class,
        'invalidContact' => InvalidContactServerException::class,
        'invalidEmail' => InvalidEmailServerException::class,
        'malformed' => MalformedServerException::class,
        'orderNotReady' => OrderNotReadyServerException::class,
        'rateLimited' => RateLimitedServerException::class,
        'rejectedIdentifier' => RejectedIdentifierServerException::class,
        'serverInternal' => InternalServerException::class,
        'tls' => TlsServerException::class,
        'unauthorized' => UnauthorizedServerException::class,
        'unknownHost' => UnknownHostServerException::class,
        'unsupportedContact' => UnsupportedContactServerException::class,
        'unsupportedIdentifier' => UnsupportedIdentifierServerException::class,
        'userActionRequired' => UserActionRequiredServerException::class,
    ];

    /**
     * Get a response summary (useful for exceptions).
     * Use Guzzle method if available (Guzzle 6.1.1+).
     *
     * @return string
     */
    public static function getResponseBodySummary(ResponseInterface $response)
    {
        // Rewind the stream if possible to allow re-reading for the summary.
        if ($response->getBody()->isSeekable()) {
            $response->getBody()->rewind();
        }

        if (method_exists(RequestException::class, 'getResponseBodySummary')) {
            return RequestException::getResponseBodySummary($response);
        }

        $body = Utils::copyToString($response->getBody());

        if (\strlen($body) > 120) {
            return substr($body, 0, 120).' (truncated...)';
        }

        return $body;
    }

    /**
     * @return AcmeCoreServerException
     */
    public function createAcmeExceptionForResponse(
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $previous = null
    ) {
        $body = Utils::copyToString($response->getBody());

        try {
            $data = JsonDecoder::decode($body, true);
        } catch (\InvalidArgumentException $e) {
            $data = null;
        }

        if (!$data || !isset($data['type'], $data['detail'])) {
            // Not JSON: not an ACME error response
            return $this->createDefaultExceptionForResponse($request, $response, $previous);
        }

        $type = preg_replace('/^urn:(ietf:params:)?acme:error:/i', '', $data['type']);

        if (!isset(self::$exceptions[$type])) {
            // Unknown type: not an ACME error response
            return $this->createDefaultExceptionForResponse($request, $response, $previous);
        }

        $exceptionClass = self::$exceptions[$type];

        return new $exceptionClass(
            $request,
            sprintf('%s (on request "%s %s")', $data['detail'], $request->getMethod(), $request->getUri()),
            $previous
        );
    }

    /**
     * @return AcmeCoreServerException
     */
    private function createDefaultExceptionForResponse(
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $previous = null
    ) {
        return new AcmeCoreServerException(
            $request,
            sprintf(
                'A non-ACME %s HTTP error occured on request "%s %s" (response body: "%s")',
                $response->getStatusCode(),
                $request->getMethod(),
                $request->getUri(),
                self::getResponseBodySummary($response)
            ),
            $previous
        );
    }
}
