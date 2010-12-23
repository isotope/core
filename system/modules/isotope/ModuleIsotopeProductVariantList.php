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


class ModuleIsotopeProductVariantList extends ModuleIsotopeProductList
{

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{		
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT VARIANT LIST ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = $this->Environment->script.'?do=modules&amp;act=edit&amp;id=' . $this->id;
			
			return $objTemplate->parse();
		}

		return parent::generate();
	}
	
	
	/**
	 * Fill the object's arrProducts array
	 * @return array
	 */
	protected function findProducts()
	{
		$this->applyFilters();
		
		// Determine category scope
		$arrCategories = $this->findCategories($this->iso_category_scope);
		
		$objVariantIds = $this->Database->prepare("SELECT DISTINCT p.id, p.pid FROM tl_iso_product_categories c, tl_iso_products p WHERE p.pid=c.pid AND published='1' AND language=''" . ($this->strFilterSQL ? " AND (" . $this->strFilterSQL . ")" : "") . " AND c.page_id IN (" . implode(',', $arrCategories) . ")" . ($this->strSearchSQL ? " AND (" . $this->strSearchSQL . ")" : "") . ($this->strOrderBySQL ? " ORDER BY " . $this->strOrderBySQL : ""))->execute($this->arrParams);
		
		$arrPID = $objVariantIds->fetchEach('pid');
		if (!count($arrPID))
			$arrPID = array(0);
		
		$objProductIds = $this->Database->prepare("SELECT DISTINCT p.id FROM tl_iso_product_categories c, tl_iso_products p WHERE p.id=c.pid AND published='1' AND p.id NOT IN (" . implode(',', $arrPID) . ")" . ($this->strFilterSQL ? " AND (" . $this->strFilterSQL . ")" : "") . " AND c.page_id IN (" . implode(',', $arrCategories) . ")" . ($this->strSearchSQL ? " AND (" . $this->strSearchSQL . ")" : "") . ($this->strOrderBySQL ? " ORDER BY " . $this->strOrderBySQL : ""))->execute($this->arrParams);
		
		$arrIds = array_merge($objVariantIds->fetchEach('id'), $objProductIds->fetchEach('id'));
		
		// Add pagination
		if ($this->perPage > 0)
		{
			$total = count($arrIds);
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
			$offset = ($page - 1) * $this->perPage;

			$objPagination = new Pagination($total, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
			
			return $this->getProducts($arrIds, true, $this->perPage, $offset);
		}
		
		return $this->getProducts($arrIds);
	}
}

