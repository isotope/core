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


/**
 * Class ContentIsotope
 *
 * Provide methods to handle Isotope content elements.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class ContentIsotope extends ContentElement
{

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;


	/**
	 * Initialize the content element
	 * @param object
	 */
	public function __construct(Database_Result $objElement)
	{
		parent::__construct($objElement);

		if (TL_MODE == 'FE')
		{
			$this->import('Isotope');

			if (FE_USER_LOGGED_IN === true)
			{
				$this->import('FrontendUser', 'User');
			}

			// Load Isotope javascript and css
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/isotope.js';
			$GLOBALS['TL_CSS'][] = 'system/modules/isotope/html/isotope.css';
		}
	}


	/**
	 * Shortcut for a single product by ID or from database result
	 * @deprecated
	 * @see IsotopeFrontend::getProduct()
	 */
	protected function getProduct($objProductData, $blnCheckAvailability=true)
	{
		trigger_error('Using ContentIsotope::getProduct() is deprecated. Please use IsotopeFrontend::getProduct()', E_USER_NOTICE);
		return IsotopeFrontend::getProduct($objProductData, IsotopeFrontend::getReaderPageId(null, $this->iso_reader_jumpTo), $blnCheckAvailability);
	}


	/**
	 * Shortcut for a single product by alias (from url?)
	 * @deprecated
	 * @see IsotopeFrontend::getProducts()
	 */
	protected function getProductByAlias($strAlias, $blnCheckAvailability=true)
	{
		trigger_error('Using ContentIsotope::getProductByAlias() is deprecated. Please use IsotopeFrontend::getProductByAlias()', E_USER_NOTICE);
		return IsotopeFrontend::getProductByAlias($strAlias, IsotopeFrontend::getReaderPageId(null, $this->iso_reader_jumpTo), $blnCheckAvailability);
	}


	/**
	 * Generate products from database result or array of IDs
	 * @deprecated
	 * @see IsotopeFrontend::getProducts()
	 */
	protected function getProducts($objProductData, $blnCheckAvailability=true, array $arrFilters=array(), array $arrSorting=array())
	{
		trigger_error('Using ContentIsotope::getProducts() is deprecated. Please use IsotopeFrontend::getProducts()', E_USER_NOTICE);
		return IsotopeFrontend::getProducts($objProductData, IsotopeFrontend::getReaderPageId(null, $this->iso_reader_jumpTo), $blnCheckAvailability, $arrFilters, $arrSorting);
	}
}

