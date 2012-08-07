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


/**
 * Class ModuleIsotopeProductList
 * The mother of all product lists.
 */
class ModuleIsotopeProductList extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_productlist';

	/**
	 * Cache products. Can be disable in a child class, e.g. a "random products list"
	 * @var boolean
	 */
	protected $blnCacheProducts = true;


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT LIST ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Hide product list in reader mode if the respective setting is enabled
		if ($this->iso_hide_list && $this->Input->get('product') != '')
		{
			return '';
		}

		$this->iso_filterModules = deserialize($this->iso_filterModules, true);
		$this->iso_productcache = deserialize($this->iso_productcache, true);

		// Disable the cache if in preview mode
		if (BE_USER_LOGGED_IN === true)
		{
			$this->blnCacheProducts = false;
		}

		return parent::generate();
	}


	/**
	 * Generate a single product and return it's HTML string
	 * @return string
	 */
	public function generateAjax()
	{
		$objProduct = IsotopeFrontend::getProduct($this->Input->get('product'), IsotopeFrontend::getReaderPageId(null, $this->iso_reader_jumpTo), false);

		if ($objProduct !== null)
		{
			return $objProduct->generateAjax($this);
		}

		return '';
	}


	/**
	 * Compile product list.
	 *
	 * This function is specially designed so you can keep it in your child classes and only override findProducts().
	 * You will automatically gain product caching (see class property), grid classes, pagination and more.
	 *
	 * @return void
	 */
	protected function compile()
	{
		$arrProducts = null;

		if ($this->blnCacheProducts)
		{
			global $objPage;
			$time = time();

			$objCache = $this->Database->prepare("SELECT * FROM tl_iso_productcache
												  WHERE page_id=? AND module_id=? AND requestcache_id=? AND (keywords=? OR keywords='') AND (expires>$time OR expires=0)
												  ORDER BY keywords=''")
									   ->limit(1)
									   ->execute($objPage->id, $this->id, (int)$this->Input->get('isorc'), (string)$this->Input->get('keywords'));

			// Cache found
			if ($objCache->numRows)
			{
				$arrCacheIds = $objCache->products == '' ? array() : explode(',', $objCache->products);

				// Use the cache if keywords match. Otherwise we will use the product IDs as a "limit" for findProducts()
				if ($objCache->keywords == $this->Input->get('keywords'))
				{
					$total = count($arrCacheIds);

					if ($this->perPage > 0)
					{
						$offset = $this->generatePagination($total);

						$total = $total - $offset;
						$total = $total > $this->perPage ? $this->perPage : $total;

						$arrProducts = IsotopeFrontend::getProducts(array_slice($arrCacheIds, $offset, $this->perPage), IsotopeFrontend::getReaderPageId(null, $this->iso_reader_jumpTo));
					}
					else
					{
						$arrProducts = IsotopeFrontend::getProducts($arrCacheIds, IsotopeFrontend::getReaderPageId(null, $this->iso_reader_jumpTo));
					}

					// Cache is wrong, drop everything and run findProducts()
					if (count($arrProducts) != $total)
					{
						$arrCacheIds = null;
						$arrProducts = null;
					}
				}
			}
		}

		if (!is_array($arrProducts))
		{
			// Display "loading products" message and add cache flag
			if ($this->blnCacheProducts)
			{
				$blnCacheMessage = (bool)$this->iso_productcache[$objPage->id][(int)$this->Input->get('isorc')];

				if ($blnCacheMessage && !$this->Input->get('buildCache'))
				{
					$this->Template = new FrontendTemplate('mod_iso_productlist_caching');
					$this->Template->message = $GLOBALS['ISO_LANG']['MSC']['productcacheLoading'];
					return;
				}

				// Start measuring how long it takes to load the products
				$start = microtime(true);

				// Load products
				$arrProducts = $this->findProducts($arrCacheIds);

				// Decide if we should show the "caching products" message the next time
				$end = microtime(true) - $start;
				$this->blnCacheProducts = $end > 1 ? true : false;

				if ($blnCacheMessage != $this->blnCacheProducts)
				{
					$arrCacheMessage = $this->iso_productcache;
					$arrCacheMessage[$objPage->id][(int) $this->Input->get('isorc')] = $this->blnCacheProducts;
					$this->Database->prepare("UPDATE tl_module SET iso_productcache=? WHERE id=?")->execute(serialize($arrCacheMessage), $this->id);
				}

				// Do not write cache if table is locked. That's the case if another process is already writing cache
				if ($this->Database->query("SHOW OPEN TABLES FROM `{$GLOBALS['TL_CONFIG']['dbDatabase']}` LIKE 'tl_iso_productcache'")->In_use == 0)
				{
					$this->Database->lockTables(array('tl_iso_productcache'=>'WRITE', 'tl_iso_products'=>'READ'));
					$arrIds = array();

					foreach ($arrProducts as $objProduct)
					{
						$arrIds[] = $objProduct->id;
					}

					$intExpires = (int) $this->Database->execute("SELECT MIN(start) AS expires FROM tl_iso_products WHERE start>$time")
													   ->expires;

					// Also delete all expired caches if we run a delete anyway
					$this->Database->prepare("DELETE FROM tl_iso_productcache WHERE (page_id=? AND module_id=? AND requestcache_id=? AND keywords=?) OR (expires>0 AND expires<$time)")
								   ->executeUncached($objPage->id, $this->id, (int)$this->Input->get('isorc'), (string)$this->Input->get('keywords'));

					$this->Database->prepare("INSERT INTO tl_iso_productcache (page_id,module_id,requestcache_id,keywords,products,expires) VALUES (?,?,?,?,?,?)")
								   ->executeUncached($objPage->id, $this->id, (int)$this->Input->get('isorc'), (string)$this->Input->get('keywords'), implode(',', $arrIds), $intExpires);

					$this->Database->unlockTables();
				}
			}
			else
			{
				$arrProducts = $this->findProducts();
			}

			if ($this->perPage > 0)
			{
				$offset = $this->generatePagination(count($arrProducts));
				$arrProducts = array_slice($arrProducts, $offset, $this->perPage);
			}
		}

		// No products found
		if (!is_array($arrProducts) || !count($arrProducts))
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noProducts'];
			return;
		}

		if ($this->iso_jump_first && $this->Input->get('product') == '')
		{
			$objProduct = array_shift($arrProducts);
			$this->redirect($objProduct->href_reader);
		}

		$arrBuffer = array();

		foreach ($arrProducts as $objProduct)
		{
			$strTemplate = $this->getProductTemplate($objProduct);
			$arrBuffer[] = array
			(
				'cssID'	=> ($objProduct->cssID[0] != '') ? ' id="' . $objProduct->cssID[0] . '"' : '',
				'class'	=> $objProduct->cssID[1],
				'html'	=> $objProduct->generate($strTemplate, $this),
			);
		}

		$this->Template->products = IsotopeFrontend::generateRowClass($arrBuffer, 'product', 'class', $this->iso_cols);
	}

	private $arrProductTemplates;
	
	protected function getProductTemplate($objProduct) {
		if(!isset($this->arrProductTemplates)) {
			$this->arrProductTemplates = deserialize($this->iso_list_layoutPerType, true);
		}
		if(strlen($this->arrProductTemplates[$objProduct->type])) {
			return $this->arrProductTemplates[$objProduct->type];
		}
		if(strlen($this->iso_list_layout)) {
			return $this->iso_list_layout;
		}
		return $objProduct->list_template;
	}

	/**
	 * Find all products we need to list.
	 * @return array
	 */
	protected function findProducts($arrCacheIds=null)
	{
		$time = time();
		$arrCategories = $this->findCategories($this->iso_category_scope);

		list($arrFilters, $arrSorting, $strWhere, $arrValues) = $this->getFiltersAndSorting();

		$objProductData = $this->Database->prepare(IsotopeProduct::getSelectStatement() . "
													WHERE p1.language=''"
													. (BE_USER_LOGGED_IN === true ? '' : " AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)")
													. "AND c.page_id IN (" . implode(',', $arrCategories) . ")"
													. (is_array($arrCacheIds) ? ("AND p1.id IN (" . implode(',', $arrCacheIds) . ")") : '')
													. ($this->iso_list_where == '' ? '' : " AND {$this->iso_list_where}")
													. "$strWhere GROUP BY p1.id ORDER BY c.sorting")
										 ->execute($arrValues);

		return IsotopeFrontend::getProducts($objProductData, IsotopeFrontend::getReaderPageId(null, $this->iso_reader_jumpTo), true, $arrFilters, $arrSorting);
	}


	/**
	 * Generate the pagination
	 * @param integer
	 * @return integer
	 */
	protected function generatePagination($total)
	{
		// Add pagination
		if ($this->perPage > 0 && $total > 0)
		{
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;

			// Check the maximum page number
			if ($page > ($total/$this->perPage))
			{
				$page = ceil($total/$this->perPage);
			}

			$offset = ($page - 1) * $this->perPage;

			$objPagination = new Pagination($total, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");

			return $offset;
		}

		return 0;
	}


	/**
	 * Get filter & sorting configuration
	 * @param boolean
	 * @return array
	 */
	protected function getFiltersAndSorting($blnNativeSQL=true)
	{
		$arrFilters = array();
		$arrSorting = array();

		if (is_array($this->iso_filterModules))
		{
			$arrModules = array_reverse($this->iso_filterModules);

			foreach ($arrModules as $module)
			{
				if (is_array($GLOBALS['ISO_FILTERS'][$module]))
				{
					$arrFilters = array_merge($arrFilters, $GLOBALS['ISO_FILTERS'][$module]);
				}

				if (is_array($GLOBALS['ISO_SORTING'][$module]))
				{
					$arrSorting = array_merge($arrSorting, $GLOBALS['ISO_SORTING'][$module]);
				}

				if ($GLOBALS['ISO_LIMIT'][$module] > 0)
				{
					$this->perPage = $GLOBALS['ISO_LIMIT'][$module];
				}
			}
		}

		if (empty($arrSorting) && $this->iso_listingSortField != '')
		{
			$arrSorting[$this->iso_listingSortField] = array(($this->iso_listingSortDirection=='DESC' ? SORT_DESC : SORT_ASC), SORT_REGULAR);
		}

		// Thanks to certo web & design for sponsoring this feature
		if ($blnNativeSQL)
		{
			$strWhere = '';
			$arrWhere = array();
			$arrValues = array();

			// Initiate native SQL filtering
			foreach ($arrFilters as $k => $filter)
			{
				if ($filter['group'] == '' && !in_array($filter['attribute'], $GLOBALS['ISO_CONFIG']['dynamicAttributes']))
				{
					$operator = IsotopeFrontend::convertFilterOperator($filter['operator'], 'SQL');
					$arrWhere[] = "{$filter['attribute']} $operator ?";
					$arrValues[] = $filter['value'];
					unset($arrFilters[$k]);
				}
			}

			if (count($arrWhere))
			{
				$time = time();
				$strWhere = " AND ((p1." . implode(' AND p1.', $arrWhere) . ") OR p1.id IN (SELECT pid FROM tl_iso_products WHERE language='' AND " . implode(' AND ', $arrWhere)
							. (BE_USER_LOGGED_IN === true ? '' : " AND published='1' AND (start='' OR start<$time) AND (stop='' OR stop>$time)") . "))";
				$arrValues = array_merge($arrValues, $arrValues);
			}

			return array($arrFilters, $arrSorting, $strWhere, $arrValues);
		}

		return array($arrFilters, $arrSorting);
	}
}

