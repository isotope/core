<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;

use Isotope\Isotope;

/**
 * Isotope\Model\Config represents an Isotope config model
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
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
        return Isotope::translate(($this->label ?: $this->name));
    }

    /**
     * Get billing address fields
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
        $arrFields = deserialize($this->billing_fields);

        if (!is_array($arrFields)) {
            return array();
        }

        return $arrFields;
    }

    /**
     * Get shipping address fields
     * @return  array
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
     * @return  array
     */
    public function getShippingFieldsConfig()
    {
        $arrFields = deserialize($this->shipping_fields);

        if (!is_array($arrFields)) {
            return array();
        }

        return $arrFields;
    }

    /**
     * Get enabled billing countries
     * @return  array
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
     * @return  array
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
     * Get the limit to mark products as new
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
     * Get url param
     * @param   string
     * @return  string
     */
    public function getUrlParam($strKey)
    {
        // auto_item support -> empty
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($strKey, $GLOBALS['TL_AUTO_ITEM'])) {

            return '';
        }

        $arrMatrix = Isotope::getConfig()->urlMatrix;

        if (!isset($arrMatrix[$strKey])) {

            return $strKey;
        }

        return $arrMatrix[$strKey];
    }

    /**
     * Find config set in root page or the fallback
     * @param  int
     * @return object|null
     */
    public static function findByRootPageOrFallback($intRoot, array $arrOptions=array())
    {
        $arrOptions = array_merge(
            array(
                'column' => array("(id=(SELECT iso_config FROM tl_page WHERE id=?) OR fallback='1')"),
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
     * @return object|null
     */
    public static function findByFallback(array $arrOptions=array())
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
