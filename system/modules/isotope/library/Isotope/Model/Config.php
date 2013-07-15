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
     * Return custom options or table row data
     * @param mixed
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'billing_fields':
            case 'shipping_fields':
                return deserialize($this->arrData[$strKey], true);

            default:
                return deserialize(parent::__get($strKey));
        }
    }

    /**
     * Get billing address fields
     * @return  array
     */
    public function getBillingFields()
    {
        // @todo: cache?
        return array_filter(array_map(
            function($field) {
                return $field['enabled'] ? $field['value'] : null;
            },
            $this->billing_fields
        ));
    }

    /**
     * Get shipping address fields
     * @return  array
     */
    public function getShippingFields()
    {
        // @todo: cache?
        return array_filter(array_map(
            function($field) {
                return $field['enabled'] ? $field['value'] : null;
            },
            $this->shipping_fields
        ));
    }

    /**
     * Get enabled billing countries
     * @return  array
     */
    public function getBillingCountries()
    {
        // @todo: cache?
        $arrCountries = deserialize($this->billing_countries);

        if (empty($arrCountries) || !is_array($arrCountries)) {
            $arrCountries = array_keys(\System::getCountries());
        }

        return $arrCountries;
    }

    /**
     * Get enabled shipping countries
     * @return  array
     */
    public function getShippingCountries()
    {
        // @todo: cache?
        $arrCountries = deserialize($this->shipping_countries);

        if (empty($arrCountries) || !is_array($arrCountries)) {
            $arrCountries = array_keys(\System::getCountries());
        }

        return $arrCountries;
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
     * Find config set in root page or the fallback
     * @param  int
     * @return object|null
     */
    public static function findByRootPageOrFallback($intRoot)
    {
        $arrOptions = array(
			'column' => array("(id=(SELECT iso_config FROM tl_page WHERE id=?) OR fallback='1')"),
			'value'  => $intRoot,
			'order'  => 'fallback',
			'return' => 'Model'
		);

		return static::find($arrOptions);
    }

    /**
     * Find the fallback config
     * @return object|null
     */
    public static function findByFallback()
    {
        $arrOptions = array(
			'column' => 'fallback',
			'value'  => '1',
			'return' => 'Model'
		);

		return static::find($arrOptions);
    }
}
