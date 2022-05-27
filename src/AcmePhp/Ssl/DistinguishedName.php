<?php

/*
 * This file is part of the Acme PHP project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daanra\LaravelLetsEncrypt\AcmePhp\Ssl;

use Webmozart\Assert\Assert;

/**
 * Represent a Distinguished Name.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class DistinguishedName
{
    /** @var string */
    private $commonName;

    /** @var string */
    private $countryName;

    /** @var string */
    private $stateOrProvinceName;

    /** @var string */
    private $localityName;

    /** @var string */
    private $organizationName;

    /** @var string */
    private $organizationalUnitName;

    /** @var string */
    private $emailAddress;

    /** @var array */
    private $subjectAlternativeNames;

    /**
     * @param string $commonName
     * @param string $countryName
     * @param string $stateOrProvinceName
     * @param string $localityName
     * @param string $organizationName
     * @param string $organizationalUnitName
     * @param string $emailAddress
     */
    public function __construct(
        $commonName,
        $countryName = null,
        $stateOrProvinceName = null,
        $localityName = null,
        $organizationName = null,
        $organizationalUnitName = null,
        $emailAddress = null,
        array $subjectAlternativeNames = []
    ) {
        Assert::stringNotEmpty($commonName, __CLASS__.'::$commonName expected a non empty string. Got: %s');
        Assert::nullOrStringNotEmpty($countryName, __CLASS__.'::$countryName expected a string. Got: %s');
        Assert::nullOrStringNotEmpty($stateOrProvinceName, __CLASS__.'::$stateOrProvinceName expected a string. Got: %s');
        Assert::nullOrStringNotEmpty($localityName, __CLASS__.'::$localityName expected a string. Got: %s');
        Assert::nullOrStringNotEmpty($organizationName, __CLASS__.'::$organizationName expected a string. Got: %s');
        Assert::nullOrStringNotEmpty($organizationalUnitName, __CLASS__.'::$organizationalUnitName expected a string. Got: %s');
        Assert::nullOrStringNotEmpty($emailAddress, __CLASS__.'::$emailAddress expected a string. Got: %s');
        Assert::allStringNotEmpty(
            $subjectAlternativeNames,
            __CLASS__.'::$subjectAlternativeNames expected an array of non empty string. Got: %s'
        );

        $this->commonName = $commonName;
        $this->countryName = $countryName;
        $this->stateOrProvinceName = $stateOrProvinceName;
        $this->localityName = $localityName;
        $this->organizationName = $organizationName;
        $this->organizationalUnitName = $organizationalUnitName;
        $this->emailAddress = $emailAddress;
        $this->subjectAlternativeNames = array_diff(array_unique($subjectAlternativeNames), [$commonName]);
    }

    /**
     * @return string
     */
    public function getCommonName()
    {
        return $this->commonName;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * @return string
     */
    public function getStateOrProvinceName()
    {
        return $this->stateOrProvinceName;
    }

    /**
     * @return string
     */
    public function getLocalityName()
    {
        return $this->localityName;
    }

    /**
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @return string
     */
    public function getOrganizationalUnitName()
    {
        return $this->organizationalUnitName;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @return array
     */
    public function getSubjectAlternativeNames()
    {
        return $this->subjectAlternativeNames;
    }
}
