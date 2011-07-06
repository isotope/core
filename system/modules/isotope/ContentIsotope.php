<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


abstract class ContentIsotope extends ContentElement
{

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;


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

			// Make sure field data is available
			$this->loadDataContainer('tl_iso_products');
			$this->loadLanguageFile('tl_iso_products');
		}
	}


	/**
	 * Shortcut for a single product by ID or database result
	 * @param  int|DB_Result
	 * @return object|null
	 */
	protected function getProduct($objProductData, $blnCheckAvailability=true)
	{
		if (is_numeric($objProductData))
		{
			$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS product_class FROM tl_iso_products WHERE language='' AND id=?")->execute($objProductData);
		}

		if (!($objProductData instanceof Database_Result) || !$objProductData->numRows)
		{
			return null;
		}

		$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];

		if ($strClass == '' || !$this->classFileExists($strClass))
		{
			return null;
		}

		$objProduct = new $strClass($objProductData->row());

		if ($blnCheckAvailability && !$objProduct->available)
		{
			return null;
		}

		global $objPage;
		$objProduct->reader_jumpTo = $this->iso_reader_jumpTo ? $this->iso_reader_jumpTo : $objPage->id;

		return $objProduct;
	}


	/**
	 * Shortcut for a single product by alias (from url?)
	 */
	protected function getProductByAlias($strAlias, $blnCheckAvailability=true)
	{
		$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS product_class FROM tl_iso_products WHERE pid=0 AND language='' AND " . (is_numeric($strAlias) ? 'id' : 'alias') . "=?")
										 ->limit(1)
										 ->executeUncached($strAlias);

		return $this->getProduct($objProductData, $blnCheckAvailability);
	}


	/**
	 * Retrieve multiple products by ID.
	 * @param  array
	 * @return array
	 */
	protected function getProducts($arrIds, $blnCheckAvailability=true)
	{
		// $objProductData can also be an array of product ids
		if (is_array($objProductData) && count($objProductData))
		{
			$objProductData = $this->Database->execute($this->Isotope->getProductSelect() . "
														WHERE p1.id IN (" . implode(',', array_map('intval', $objProductData)) . ")
														ORDER BY p1.id=" . implode(' DESC, p1.id=', $objProductData) . " DESC");
		}

		if (!($objProductData instanceof Database_Result) || !$objProductData->numRows)
		{
			return array();
		}

		$arrProducts = array();

		while( $objProductData->next() )
		{
			$objProduct = $this->getProduct($objProductData, $blnCheckAvailability);

			if ($objProduct instanceof IsotopeProduct)
			{
				$arrProducts[$objProductData->id] = $objProduct;
			}
		}

		return $arrProducts;
	}
}

