<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\VatNoValidator;

use Contao\System;
use Isotope\Interfaces\IsotopeVatNoValidator;
use Isotope\Model\Address;
use Isotope\Model\TaxRate;


/**
 * Class EuViesValidator
 *
 * @see https://github.com/quimateur/vies-vat-validator
 */
class EuViesValidator implements IsotopeVatNoValidator
{
    private $soap;
    private static $cache = array();

    /**
     * WSDL VIES Url Service
     *
     * @var string
     */
    private static $url_vies = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * Valid european countries ISO codes
     *
     * @var array
     */
    private static $european_countries = array('AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK');


    public function __construct ()
    {
        try {
            $this->soap = new \SoapClient(static::$url_vies, ['exceptions' => true]);
        } catch (\SoapFault $e) {
            $this->soap = false;
            System::log('EU VAT validation failed: ' . $e->getMessage(), __METHOD__, TL_ERROR);
        }
    }

    /**
     * Return true if vat number could be validated, false if not
     *
     * @param Address $address
     *
     * @return bool
     * @throws \RuntimeException if address country does not match VAT country
     */
    public function validate(Address $address)
    {
        $vatNo = (string) $address->vat_no;

        if ($vatNo === '') {
            return false;
        }

        $addressCountry = strtoupper($address->country);
        $vatCountry = $this->prepareVatCountry($vatNo);
        $vatId = $this->prepareVatId($vatNo);

        if (!\in_array($addressCountry, static::$european_countries)) {
            return false;
        }

        if ($addressCountry !== $vatCountry) {
            throw new \RuntimeException('Vat No country does not match address country.');
            // TODO: add to language files
        }

        return $this->checkVat($vatCountry, $vatId);
    }

    /**
     * Check if tax should be exempted because of a valid tax number
     *
     * @param Address $address
     * @param TaxRate $tax
     *
     * @return bool
     */
    public function exemptTax(Address $address, TaxRate $tax)
    {
        try {
            return $this->validate($address);
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Check if it's a valid vat number.
     *
     * @param string $country
     * @param string $number
     *
     * @return bool
     */
    private function checkVat($country, $number)
    {
        if (false === $this->soap) {
            return false;
        }

        $key = $country . $number;

        if (!isset(static::$cache[$key])) {
            $vat = array(
                'vatNumber'   => $number,
                'countryCode' => $country,
            );

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                static::$cache[$key] = $this->soap->checkVat($vat);
            } catch (\SoapFault $e) {
                static::$cache[$key] = (object) array_merge(
                    $vat,
                    array(
                        'requestDate' => date('Y-m-d').'+01:00',
                        'valid'       => false,
                        'name'        => '---',
                        'address'     => '---',
                    )
                );
            }
        }

        return static::$cache[$key]->valid;
    }

    /**
     * Get normalized VAT country out of VAT number
     *
     * @param $vatNo
     *
     * @return string
     */
    private function prepareVatCountry($vatNo)
    {
        return strtoupper(substr($vatNo, 0, 2));
    }

    /**
     * Get normalized VAT ID out of VAT number
     *
     * @param $vatNo
     *
     * @return string
     */
    private function prepareVatId($vatNo)
    {
        $vatId = substr($vatNo, 2);
        $vatId = str_replace(array(' ', '.'), '', $vatId);
        $vatId = strtoupper($vatId);

        return $vatId;
    }
}
