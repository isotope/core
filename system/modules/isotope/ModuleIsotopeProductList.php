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


class ModuleIsotopeProductList extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_productlist';
	
	/**
	 * Cache products. Can be disable in a child class, e.g. a "random products list"
	 * @var bool
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

		if ($this->iso_cols < 1)
		{
			$this->iso_cols = 1;
		}

		$this->iso_filterModules = deserialize($this->iso_filterModules, true);
		$this->iso_productcache = deserialize($this->iso_productcache, true);

		return parent::generate();
	}


	public function generateAjax()
	{
		$objProduct = $this->getProduct($this->Input->get('product'), false);

		if ($objProduct instanceof IsotopeProduct)
		{
			return $objProduct->generateAjax($this);
		}

		return '';
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		$arrProducts = null;

		if ($this->blnCacheProducts)
		{
			global $objPage;
			$arrProductCache = $this->Database->prepare("SELECT product_id FROM tl_iso_productcache WHERE page_id=? AND module_id=? AND requestcache_id=? ORDER BY id ASC")
											  ->execute($objPage->id, $this->id, (int)$this->Input->get('isorc'))
											  ->fetchEach('product_id');

			$total = count($arrProductCache);
			
			if ($total > 0)
			{
				$offset = $this->generatePagination($total);
				$arrProducts = $this->getProducts(array_slice($arrProductCache, $offset, $this->perPage));

				$expected = ($total - $offset);
				$expected = $expected > $this->perPage ? $this->perPage : $expected;

				// Cache is wrong, drop everything and run findProducts()
				if (count($arrProducts) != $expected)
				{
					$arrProducts = null;
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
				$arrProducts = $this->findProducts();
				
				if (is_array($arrProducts) && count($arrProducts))
				{
					// Decide if we should cache the products
					$end = microtime(true) - $start;
					$this->blnCacheProducts = $end > 1 ? true : false;
					if ($blnCacheMessage != $this->blnCacheProducts)
					{
						$arrCacheMessage = $this->iso_productcache;
						$arrCacheMessage[$objPage->id][(int)$this->Input->get('isorc')] = $this->blnCacheProducts;
						$this->Database->prepare("UPDATE tl_module SET iso_productcache=? WHERE id=?")->execute(serialize($arrCacheMessage), $this->id);
					}
	
					// Do not write cache if table is locked. That's the case if another process is already writing cache
					if ($this->Database->query("SHOW OPEN TABLES FROM {$GLOBALS['TL_CONFIG']['dbDatabase']} LIKE 'tl_iso_productcache'")->In_use == 0)
					{
						$this->Database->lockTables(array('tl_iso_productcache'=>'WRITE'));
						$time = time();
						$arrIds = array();
		
						foreach( $arrProducts as $objProduct )
						{
							$arrIds[] = $objProduct->id;
						}
		
						$this->Database->execute("DELETE FROM tl_iso_productcache WHERE page_id={$objPage->id} AND module_id={$this->id} AND requestcache_id=".(int)$this->Input->get('isorc'));
						$this->Database->execute("INSERT INTO tl_iso_productcache (tstamp,page_id,module_id,requestcache_id,product_id) VALUES ($time, {$objPage->id}, {$this->id}, " . (int)$this->Input->get('isorc') . "," . implode("), ($time, {$objPage->id}, {$this->id}, " . (int)$this->Input->get('isorc') . ",", $arrIds) . ")");
						$this->Database->unlockTables();
					}
				}
			}
			else
			{
				$arrProducts = $this->findProducts();
			}
			
			$offset = $this->generatePagination(count($arrProducts));
			$arrProducts = array_slice($arrProducts, $offset, $this->perPage);
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
		$total = count($arrProducts) - 1;
		$current = 0;
		$row = 0;
		$col = 0;
		$rows = ceil(count($arrProducts) / $this->iso_cols) - 1;
		$cols = $this->iso_cols - 1;
		foreach( $arrProducts as $objProduct )
		{
			$blnClear = false;

			if ($current > 0 && $current % $this->iso_cols == 0)
			{
				$blnClear = true;
				++$row;
				$col = 0;
			}

			$strClass = 'product product_'.$current . ($current%2 ? ' product_even' : ' product_odd') . ($current == 0 ? ' product_first' : '') . ($current == $total ? ' product_last' : '');

			// Add row & col classes
			if ($this->iso_cols > 1)
			{
				$strClass .= ' row_'.$row . ($row%2 ? ' row_even' : ' row_odd') . ($row == 0 ? ' row_first' : '') . ($row == $rows ? ' row_last' : '');
				$strClass .= ' col_'.$col . ($col%2 ? ' col_even' : ' col_odd') . ($col == 0 ? ' col_first' : '') . ($col == $cols ? ' col_last' : '');
			}

			$arrBuffer[] = array
			(
				'clear'		=> (($this->iso_cols > 1 && $blnClear) ? true : false),
				'class'		=> $strClass,
				'html'		=> $objProduct->generate((strlen($this->iso_list_layout) ? $this->iso_list_layout : $objProduct->list_template), $this),
			);

			++$col;
			++$current;
		}

		$this->Template->products = $arrBuffer;
	}


	/**
	 * Find all products we need to list.
	 * @param	void
	 * @return	array
	 */
	protected function findProducts()
	{
		$arrIds = $this->findCategoryProducts($this->iso_category_scope);

		$objProductData = $this->Database->execute($this->Isotope->getProductSelect() . " WHERE p1.published='1' AND p1.language='' AND p1.id IN (" . implode(',', $arrIds) . ")");

		list($arrFilters, $arrSorting) = $this->getFiltersAndSorting();

		return $this->getProducts($objProductData, true, $arrFilters, $arrSorting);
	}
	
	
	/**
	 * Generate the pagination
	 *
	 * @param	int	$total
	 * @return	int
	 */
	protected function generatePagination($total)
	{
		// Add pagination
		if ($this->perPage > 0)
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
	 */
	protected function getFiltersAndSorting()
	{
		if (!is_array($this->iso_filterModules))
		{
			return array(array(), array());
		}

		$arrFilters = array();
		$arrSorting = array();
		$arrModules = array_reverse($this->iso_filterModules);

		foreach( $arrModules as $module )
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

		return array($arrFilters, $arrSorting);
	}
}

