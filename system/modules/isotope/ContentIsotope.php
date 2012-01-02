<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class ContentIsotope
 * 
 * Provide methods to handle Isotope content elements.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
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

			if (FE_USER_LOGGED_IN)
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

