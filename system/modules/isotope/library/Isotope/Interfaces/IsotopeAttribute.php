<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Interfaces;


/**
 * IsotopeAttribute is a product attribute for Isotope eCommerce
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
interface IsotopeAttibute
{

	/**
	 * Return true if attribute is a variant option
	 * @return	bool
	 */
	public function isVariantOption();

	/**
	 * Return true if attribute is customer defined
	 * @return	bool
	 */
	public function isCustomerDefined();

	/**
	 * Return SQL definition for this attribute configuration
	 * @return	string
	 */
	public function getSQLDefinition();

	/**
	 * Return array of DCA field definition
	 * @return	array
	 */
	public function getDCAFieldDefinition();

	/**
	 * Return class name for the backend widget or false if none should be available
	 * @return	string|false
	 */
	public function getBackendWidget();

	/**
	 * Return class name for the frontend widget or false if none should be available
	 * @return	string|false
	 */
	public function getFrontendWidget();
}
