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
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class ModuleIsotopeRelatedProducts extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_relatedproducts';
	
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### RELATED PRODUCTS ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		if (!strlen($this->Input->get('product')))
			return '';
		
		$this->iso_related_categories = deserialize($this->iso_related_categories);
		
		if (!is_array($this->iso_related_categories) || !count($this->iso_related_categories))
			return '';
		
		return parent::generate();
	}
	
	
	protected function compile()
	{
		$arrIds = array(0);
		$arrJumpTo = array();
		$objCategories = $this->Database->prepare("SELECT * FROM tl_iso_related_products WHERE pid IN (SELECT id FROM tl_iso_products WHERE alias=?) AND category IN (" . implode(',', $this->iso_related_categories) . ") ORDER BY id=" . implode(' DESC, id=', $this->iso_related_categories) . " DESC")->execute($this->Input->get('product'), $objProduct->id);
		
		while( $objCategories->next() )
		{
			$ids = deserialize($objCategories->products);
			
			if (is_array($ids) && count($ids))
			{
				$arrIds = array_unique(array_merge($arrIds, $ids));
				
				if ($objCategories->jumpTo)
				{
					$arrJumpTo = array_merge(array_fill_keys(array_map('strval', $ids), $objCategories->jumpTo), $arrJumpTo);
				}
			}
		}
		
		$objProductIds = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE published='1' AND id IN (" . implode(',', $arrIds) . ")");
		
		// Add pagination
		if ($this->perPage > 0)
		{
			$total = $objProductIds->execute()->numRows;
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
			$offset = ($page - 1) * $this->perPage;

			$objPagination = new Pagination($total, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
			
			$objProductIds->limit($this->perPage, $offset);
		}
		
		$arrProducts = $this->getProducts($objProductIds->execute()->fetchEach('id'));
			
		if (!is_array($arrProducts) || !count($arrProducts))
		{
			return;
		}
		
		global $objPage;
		$arrBuffer = array();
		
		foreach( $arrProducts as $i => $objProduct )
		{
			$objProduct->reader_jumpTo = $arrJumpTo[$objProduct->id] ? $arrJumpTo[$objProduct->id] : $objPage->id;
			$arrBuffer[] = array
			(
				'class'		=> (($i%2 ? 'even' : 'odd') . ($i == 0 ? ' product_first' : '')),
				'html'		=> $objProduct->generate((strlen($this->iso_list_layout) ? $this->iso_list_layout : $objProduct->list_template), $this),
			);
			
			$blnSetClear = (($i+1) % $this->columns==0 ? true : false);
		}
	
		// Add "product_last" css class
		if (count($arrBuffer))
		{
			$arrBuffer[count($arrBuffer)-1]['class'] .= ' product_last';
		}
		
		$this->Template->products = $arrBuffer;
	}
}

