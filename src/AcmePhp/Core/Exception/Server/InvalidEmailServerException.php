<?php

/*
 * This file is part of the Acme PHP project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\Server;

use Daanra\LaravelLetsEncrypt\AcmePhp\Core\Exception\AcmeCoreServerException;
use Psr\Http\Message\RequestInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class InvalidEmailServerException extends AcmeCoreServerException
{
    public function __construct(RequestInterface $request, $detail, \Exception $previous = null)
    {
        parent::__construct(
            $request,
            '[invalidEmail] This email is unacceptable (e.g., it is invalid): '.$detail,
            $previous
        );
    }
}
