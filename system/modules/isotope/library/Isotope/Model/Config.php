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

namespace Isotope\Model;

use Isotope\Translation;

/**
 * Isotope\Model\Config represents an Isotope config model
 *
 * @property int    id
 * @property int    tstamp
 * @property string name
 * @property string label
 * @property bool   fallback
 * @property string firstname
 * @property string lastname
 * @property string company
 * @property string vat_no
 * @property string street_1
 * @property string street_2
 * @property string street_3
 * @property string postal
 * @property string city
 * @property string subdivision
 * @property string country
 * @property string phone
 * @property string email
 * @property array  address_fields
 * @property string billing_country
 * @property string shipping_country
 * @property array  billing_countries
 * @property array  shipping_countries
 * @property bool   limitMemberCountries
 * @property array  vatNoValidators
 * @property string priceDisplay
 * @property string currencyFormat
 * @property int    priceRoundPrecision
 * @property string priceRoundIncrement
 * @property float  cartMinSubtotal
 * @property string currency
 * @property string currencySymbol
 * @property bool   currencySpace
 * @property string currencyPosition
 * @property string priceCalculateFactor
 * @property string priceCalculateMode
 * @property bool   currencyAutomator
 * @property string currencyOrigin
 * @property string currencyProvider
 * @property string orderPrefix
 * @property int    orderDigits
 * @property int    orderstatus_new
 * @property int    orderstatus_error
 * @property string templateGroup
 * @property array  newProductPeriod
 * @property bool   ga_enable
 * @property string ga_account
 * @property string ga_member
 */
class Config extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_config';

    /**
     * Cache for additional methods
     * @var array
     */
    protected $arrCache = array();

    /**
     * Get translated label for the config
     * @return  string
     */
    public function getLabel()
    {
        return Translation::get(($this->label ? : $this->name));
    }

    /**
     * Get billing address fields
     *
     * @return  array
     */
    public function getBillingFields()
    {
        if (!isset($this->arrCache['billingFields'])) {

            $this->arrCache['billingFields'] = array_filter(array_map(
                function($field) {
                    return $field['enabled'] ? $field['value'] : null;
                },
                $this->getBillingFieldsConfig()
            ));

        }

        return $this->arrCache['billingFields'];
    }

    /**
     * Return raw billing field data
     * @return  array
     */
    public function getBillingFieldsConfig()
    {
        if (!isset($this->arrCache['billingFieldsConfig'])) {

            $this->arrCache['billingFieldsConfig'] = array();
            $arrFields                             = deserialize($this->address_fields);

            if (is_array($arrFields)) {
                foreach ($arrFields as $arrField) {

                    $this->arrCache['billingFieldsConfig'][] = array(
                        'value'     => $arrField['name'],
                        'enabled'   => ($arrField['billing'] != 'disabled'),
                        'mandatory' => ($arrField['billing'] == 'mandatory'),
                    );
                }
            }
        }

        return $this->arrCache['billingFieldsConfig'];
    }

    /**
     * Get shipping address fields
     *
     * @return array
     */
    public function getShippingFields()
    {
        if (!isset($this->arrCache['shippingFields'])) {

            $this->arrCache['shippingFields'] = array_filter(array_map(
                function($field) {
                    return $field['enabled'] ? $field['value'] : null;
                },
                $this->getShippingFieldsConfig()
            ));
        }

        return $this->arrCache['shippingFields'];
    }

    /**
     * Return raw shipping field data
     *
     * @return array
     */
    public function getShippingFieldsConfig()
    {
        if (!isset($this->arrCache['shippingFieldsConfig'])) {

            $this->arrCache['shippingFieldsConfig'] = array();
            $arrFields                              = deserialize($this->address_fields);

            if (is_array($arrFields)) {
                foreach ($arrFields as $arrField) {

                    $this->arrCache['shippingFieldsConfig'][] = array(
                        'value'     => $arrField['name'],
                        'enabled'   => ($arrField['shipping'] != 'disabled'),
                        'mandatory' => ($arrField['shipping'] == 'mandatory'),
                    );
                }
            }
        }

        return $this->arrCache['shippingFieldsConfig'];
    }

    /**
     * Get enabled billing countries
     *
     * @return array
     */
    public function getBillingCountries()
    {
        if (!isset($this->arrCache['billingCountries'])) {

            $arrCountries = deserialize($this->billing_countries);

            if (empty($arrCountries) || !is_array($arrCountries)) {
                $arrCountries = array_keys(\System::getCountries());
            }

            $this->arrCache['billingCountries'] = $arrCountries;
        }

        return $this->arrCache['billingCountries'];
    }

    /**
     * Get enabled shipping countries
     *
     * @return array
     */
    public function getShippingCountries()
    {
        if (!isset($this->arrCache['shippingCountries'])) {

            $arrCountries = deserialize($this->shipping_countries);

            if (empty($arrCountries) || !is_array($arrCountries)) {
                $arrCountries = array_keys(\System::getCountries());
            }

            $this->arrCache['shippingCountries'] = $arrCountries;
        }

        return $this->arrCache['shippingCountries'];
    }

    /**
     * Get the price display configuration
     *
     * @return string
     */
    public function getPriceDisplay()
    {
        $format = $this->priceDisplay;

        // !HOOK: calculate price
        if (isset($GLOBALS['ISO_HOOKS']['priceDisplay']) && is_array($GLOBALS['ISO_HOOKS']['priceDisplay'])) {
            foreach ($GLOBALS['ISO_HOOKS']['priceDisplay'] as $callback) {
                $format = \System::importStatic($callback[0])->$callback[1]($format, $this);
            }
        }

        return $format;
    }

    /**
     * Get the limit to mark products as new
     *
     * @return int
     */
    public function getNewProductLimit()
    {
        if (!isset($this->arrCache['newProductLimit'])) {

            $arrPeriod = deserialize($this->newProductPeriod);

            if (!empty($arrPeriod) && is_array($arrPeriod) && $arrPeriod['value'] > 0 && $arrPeriod['unit'] != '') {
                $this->arrCache['newProductLimit'] = strtotime('-' . $arrPeriod['value'] . ' ' . $arrPeriod['unit'] . ' 00:00:00');
            } else {
                $this->arrCache['newProductLimit'] = time();
            }
        }

        return $this->arrCache['newProductLimit'];
    }

    /**
     * Find config set in root page or the fallback
     *
     * @param int   $intRoot
     * @param array $arrOptions
     *
     * @return object|null
     */
    public static function findByRootPageOrFallback($intRoot, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrOptions = array_merge(
            array(
                 'column' => array("($t.id=(SELECT iso_config FROM tl_page WHERE id=?) OR $t.fallback='1')"),
                 'value'  => $intRoot,
                 'order'  => 'fallback',
                 'return' => 'Model'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }

    /**
     * Find the fallback config
     *
     * @param array $arrOptions
     *
     * @return object|null
     */
    public static function findByFallback(array $arrOptions = array())
    {
        $arrOptions = array_merge(
            array(
                 'column' => 'fallback',
                 'value'  => '1',
                 'return' => 'Model'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }
}
