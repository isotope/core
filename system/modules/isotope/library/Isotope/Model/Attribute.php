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
 * Attribute represents a product attribute in Isotope eCommerce
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class Attribute extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_attributes';

    /**
     * Interface to validate attribute
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeAttribute';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

	/**
	 * Return true if attribute is a variant option
	 * @return	bool
	 */
	public function isVariantOption()
	{
		return (bool) $this->variant_option;
	}

	/**
	 * Return true if attribute is customer defined
	 * @return	bool
	 */
	public function isCustomerDefined()
	{
		return (bool) $this->customer_defined;
	}

	/**
	 * Return array of DCA field definition
	 * @return	array
	 */
	public function getDCAFieldDefinition()
	{
		return $this->arrData;
	}

	/**
	 * Return class name for the backend widget or false if none should be available
	 * @return	string
	 */
	public function getBackendWidget()
	{
		if (!isset($GLOBALS['BE_FFL'][$this->type])) {
			throw new \LogicException('Backend widget for attribute type "' . $this->type . '" does not exist.');
		}

		return $GLOBALS['BE_FFL'][$this->type];
	}

	/**
	 * Return class name for the frontend widget or false if none should be available
	 * @return	string
	 */
	public function getFrontendWidget()
	{
		if (!isset($GLOBALS['TL_FFL'][$this->type])) {
			throw new \LogicException('Frontend widget for attribute type "' . $this->type . '" does not exist.');
		}

		return $GLOBALS['TL_FFL'][$this->type];
	}
}
