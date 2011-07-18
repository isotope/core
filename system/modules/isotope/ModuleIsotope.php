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


abstract class ModuleIsotope extends Module
{

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;

	/**
	 * Disable caching of the frontend page if this module is in use.
	 * Usefule to enable in a child classes.
	 * @var bool
	 */
	protected $blnDisableCache = false;


	public function __construct(Database_Result $objModule, $strColumn='main')
	{
		parent::__construct($objModule, $strColumn);

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

			// Disable caching for pages with certain modules (eg. Cart)
			if ($this->blnDisableCache)
			{
				global $objPage;
				$objPage->cache = 0;
			}
		}
	}


	/**
	 * Include messages if enabled
	 * @return string
	 */
	public function generate()
	{
		$strBuffer = parent::generate();

		// Prepend any messages to the module output
		if ($this->iso_includeMessages)
		{
			$this->import('IsotopeFrontend');
			$strBuffer = $this->IsotopeFrontend->getIsotopeMessages() . $strBuffer;
		}

		return $strBuffer;
	}


	/**
	 * Shortcut for a single product by ID or from database result
	 * @param  int|object
	 * @return object|null
	 */
	protected function getProduct($objProductData, $blnCheckAvailability=true)
	{
		if (is_numeric($objProductData))
		{
			$objProductData = $this->Database->prepare($this->Isotope->getProductSelect() . " WHERE p1.language='' AND p1.id=?")->execute($objProductData);
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
	 * @param	string	$strAlias
	 * @param	bool	$blnCheckAvailability
	 * @return	mixed
	 */
	protected function getProductByAlias($strAlias, $blnCheckAvailability=true)
	{
		$objProductData = $this->Database->prepare($this->Isotope->getProductSelect() . " WHERE p1.pid=0 AND p1.language='' AND p1." . (is_numeric($strAlias) ? 'id' : 'alias') . "=?")
										 ->limit(1)
										 ->executeUncached($strAlias);

		return $this->getProduct($objProductData, $blnCheckAvailability);
	}


	/**
	 * Generate products from database result or array of IDs.
	 * @param	object|array	$objProductData
	 * @param	bool			$blnCheckAvailability
	 * @return	array
	 */
	protected function getProducts($objProductData, $blnCheckAvailability=true, array $arrFilters=array(), array $arrSorting=array())
	{
		// $objProductData can also be an array of product ids
		if (is_array($objProductData) && count($objProductData))
		{
			$objProductData = $this->Database->execute($this->Isotope->getProductSelect() . "
														WHERE p1.language='' AND p1.id IN (" . implode(',', array_map('intval', $objProductData)) . ")
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

		if (count($arrFilters))
		{
			global $filterConfig;
			$filterConfig = $arrFilters;
			$arrProducts = array_filter($arrProducts, array($this, 'filterProducts'));
		}

		if (count($arrSorting))
		{
			$arrParam = array();

			foreach( $arrSorting as $strField => $arrConfig )
			{
				$arrData = array();
				foreach( $arrProducts as $id => $objProduct )
				{
					$arrData[$id] = str_replace('"', '', $objProduct->$strField);
				}

				$arrParam[] = $arrData;
				$arrParam = array_merge($arrParam, $arrConfig);
			}

			// Add product array as the last item. This will sort the products array based on the sorting of the passed in arguments.
			$arrParam[] = &$arrProducts;

			// we need to use call_user_func_array because the number of parameters can be dynamic and this is the only way I know to pass an array as arguments
			call_user_func_array('array_multisort', $arrParam);
		}

		return $arrProducts;
	}


	/**
	 * The ids of all pages we take care of. This is what should later be used eg. for filter data.
	 */
	protected function findCategoryProducts($strCategoryScope)
	{
		if ($this->defineRoot && $this->rootPage > 0)
		{
			$objPage = $this->getPageDetails($this->rootPage);
		}
		else
		{
			global $objPage;
		}

		switch($strCategoryScope)
		{
			case 'global':
				$arrCategories = $this->getChildRecords($objPage->rootId, 'tl_page');
				$arrCategories[] = $objPage->rootId;
				break;

			case 'current_and_first_child':
				$arrCategories = $this->Database->execute("SELECT id FROM tl_page WHERE pid={$objPage->id}")->fetchEach('id');
				$arrCategories[] = $objPage->id;
				break;

			case 'current_and_all_children':
				$arrCategories = $this->getChildRecords($objPage->id, 'tl_page');
				$arrCategories[] = $objPage->id;
				break;

			case 'parent':
				$arrCategories = array($objPage->pid);
				break;

			case 'product':
				$objProduct = $this->getProductByAlias($this->Input->get('product'));

				if ($objProduct instanceof IsotopeProduct)
				{
					$arrCategories = $objProduct->categories;
				}
				else
				{
					return array(0);
				}
				break;

			case 'current_category':
			default:
				$arrCategories = array($objPage->id);
				break;
		}

		$arrIds = $this->Database->execute("SELECT pid FROM tl_iso_product_categories WHERE page_id IN (" . implode(',', $arrCategories) . ")")->fetchEach('pid');

		return count($arrIds) ? $arrIds : array(0);
	}


	/**
	 * Callback function to filter products
	 * @param	object	$objProduct
	 * @return	bool
	 */
	private function filterProducts($objProduct)
	{
		global $filterConfig;

		if (!is_array($filterConfig) || !count($filterConfig))
		{
			return true;
		}

		$arrGroups = array();

		foreach( $filterConfig as $filter )
		{
			$varValue = $objProduct->{$filter['attribute']};
			$blnMatch = false;

			// If the attribute is not set for this product, the filter does not match
			if (is_null($varValue))
			{
				return false;
			}

			switch( $filter['operator'] )
			{
				case 'like':
				case 'search':
					if (stripos($varValue, $filter['value']) !== false)
					{
						$blnMatch = true;
					}
					break;

				case '>':
				case 'gt':
					if ($varValue > $filter['value'])
					{
						$blnMatch = true;
					}
					break;

				case '<':
				case 'lt':
					if ($varValue < $filter['value'])
					{
						$blnMatch = true;
					}
					break;

				case '!=':
				case 'neq':
				case 'not':
					if ($varValue != $filter['value'])
					{
						$blnMatch = true;
					}
					break;

				case '=':
				case '==':
				case 'eq':
				default:
					if ($varValue == $filter['value'])
					{
						$blnMatch = true;
					}
			}

			if ($filter['group'])
			{
				$arrGroups[$filter['group']] = $arrGroups[$filter['group']] ? $arrGroups[$filter['group']] : $blnMatch;
			}
			elseif (!$blnMatch)
			{
				return false;
			}
		}

		if (count($arrGroups) && in_array(false, $arrGroups))
		{
			return false;
		}

		return true;
	}
}

