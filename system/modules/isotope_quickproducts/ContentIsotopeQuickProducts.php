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


class ContentIsotopeQuickProducts extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_iso_products';  
        
        
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{	
		$arrProductIds = deserialize($this->productsAlias);
		$arrProducts = $this->getProducts($arrProductIds);
		if (TL_MODE == 'BE')
		{
			foreach($arrProducts as $i => $objProduct)
			{
				$strLink .= $objProduct->name . '<br />';
			}
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE QUICK PRODUCTS ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $strLink;		
			return $objTemplate->parse();
		}
		return parent::generate();
	}
	
	/**
	 * Generate content element
	 */
	protected function compile()
	{	
	
		$arrProductIds = deserialize($this->productsAlias);
		$arrProducts = $this->getProducts($arrProductIds);				
					
		if (!is_array($arrProducts) || !count($arrProducts))
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['noProducts'];
			return;
		}
		
		$arrBuffer = array();
		
		foreach( $arrProducts as $i => $objProduct )
		{
			$arrBuffer[] = array
			(
				'class'		=> ('product' . ($i == 0 ? ' product_first' : '')),
				'html'		=> $objProduct->generate((strlen($this->iso_list_layout) ? $this->iso_list_layout : $objProduct->list_template), $this),
			);
			
		}
	
		$this->Template->products = $arrBuffer;
	}
	
	/**
	 * Retrieve multiple products by ID.
	 */
	protected function getProducts($arrIds)
	{
		if (!is_array($arrIds) || !count($arrIds))
			return array();
		
		$arrProducts = array();
		
		foreach( $arrIds as $intId )
		{
			$objProduct = $this->getProduct($intId);
		
			if (is_object($objProduct))
				$arrProducts[] = $objProduct;
		}
		
		return $arrProducts;
	}
	
	
	/**
	 * Shortcut for a single product by ID
	 */
	protected function getProduct($intId)
	{
		$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS product_class FROM tl_iso_products WHERE id=?")
										 ->limit(1)
										 ->executeUncached($intId);
									 
		$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];
		
		if (!$this->classFileExists($strClass))
		{
			return null;
		}
									
		$objProduct = new $strClass($objProductData->row());
		
		$objProduct->reader_jumpTo = $this->iso_reader_jumpTo;
			
		return $objProduct;
	}
}

