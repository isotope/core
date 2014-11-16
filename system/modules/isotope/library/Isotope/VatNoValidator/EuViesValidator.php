<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\VatNoValidator;

use Isotope\Interfaces\IsotopeVatNoValidator;
use Isotope\Model\Address;


/**
 * Class EuViesValidator
 *
 * @see https://github.com/quimateur/vies-vat-validator
 */
class EuViesValidator implements IsotopeVatNoValidator
{
    private $soap;

    /**
     * WSDL VIES Url Service
     *
     * @type string
     */
    private static $url_vies = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * Valid european countries ISO codes
     *
     * @type array
     */
    private static $european_countries = array('AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK');


    public function __construct ()
    {
        $this->soap = new \SoapClient(static::$url_vies);
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
        $vatCountry = strtoupper(substr($vatNo, 0, 2));
        $vatId = substr($vatNo, 2);

        if (!in_array($addressCountry, static::$european_countries)) {
            return false;
        }

        if ($addressCountry !== $vatCountry) {
            throw new \RuntimeException('Vat No country does not match address country.');
            // TODO: add to language files
        }

        return $this->checkVat($vatCountry, $vatId);
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
        $vat = array(
            'vatNumber'   => $number,
            'countryCode' => $country,
        );

        return (bool) $this->soap->checkVat($vat)->valid;
    }
}
