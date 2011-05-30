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
			$objTemplate->href = $this->Environment->script.'?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		if ($this->iso_cols < 1)
		{
			$this->iso_cols = 1;
		}

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
	 * Find all products we need to list.
	 * @param	void
	 * @return	array
	 */
	protected function findProducts()
	{
		$arrCategories = $this->findCategories($this->iso_category_scope);
		
		$arrFilters = array();
		$arrSorting = array();
		$this->iso_filterModules = deserialize($this->iso_filterModules, true);
		
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

		$objProductIds = $this->Database->prepare("SELECT DISTINCT p.id FROM tl_iso_product_categories c, tl_iso_products p WHERE p.id=c.pid" . (BE_USER_LOGGED_IN ? '' : " AND published='1'") . " AND c.page_id IN (" . implode(',', $arrCategories) . ")")->execute();

		$arrProducts = $this->getProducts($objProductIds->fetchEach('id'), true, $arrFilters, $arrSorting);

		return $arrProducts;
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		$arrProducts = $this->findProducts();

		if (!is_array($arrProducts) || !count($arrProducts))
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noProducts'];
			return;
		}
		
		// Add pagination
		if ($this->perPage > 0)
		{
			$total = count($arrProducts);
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;

			// Check the maximum page number
			if ($page > ($total/$this->perPage))
			{
				$page = ceil($total/$this->perPage);
			}

			$offset = ($page - 1) * $this->perPage;

			$objPagination = new Pagination($total, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");

			$arrProducts = array_slice($arrProducts, $offset, $this->perPage);
		}

		$arrBuffer = array();
		$last = count($arrProducts) - 1;
		$row = 0;
		$col = 0;
		$rows = ceil(count($arrProducts) / $this->iso_cols) - 1;
		$cols = $this->iso_cols - 1;
		foreach( $arrProducts as $i => $objProduct )
		{
			$blnClear = false;

			if ($i > 0 && $i % $this->iso_cols == 0)
			{
				$blnClear = true;
				++$row;
				$col = 0;
			}
			
			$strClass = 'product product_'.$i . ($i%2 ? ' product_even' : ' product_odd') . ($i == 0 ? ' product_first' : '') . ($i == $last ? ' product_last' : '');
			
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
		}

		$this->Template->products = $arrBuffer;
	}
}

