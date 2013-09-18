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
interface IsotopeAttribute
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
	 * Return class name for the backend widget or empty if none should be available
	 * @return	string
	 */
	public function getBackendWidget();

	/**
	 * Return class name for the frontend widget or empty if none should be available
	 * @return	string
	 */
	public function getFrontendWidget();

	/**
	 * Load attribute configuration from given DCA array
	 * @param   array
	 */
	public function loadFromDCA(array &$arrData, $strName);

	/**
	 * Save attribute configuration into the given DCA array
	 * @param	array
	 */
	public function saveToDCA(array &$arrData);

	/**
	 * Generate attribute for given product
	 * @param   IsotopeProduct
	 * @param   array
	 * @return  string
	 */
	public function generate(IsotopeProduct $objProduct, array $arrOptions=array());
}
