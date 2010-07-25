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
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();
	
	
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
			if (!is_array($GLOBALS['TL_DCA']['tl_iso_products']['fields']))
			{
				$this->loadDataContainer('tl_iso_products');
				$this->loadLanguageFile('tl_iso_products');
			}
		}
	}
			
	
	/**
	 * Shortcut for a single product by ID
	 */
	protected function getProduct($intId)
	{
		global $objPage;
		
		$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS type_class FROM tl_iso_products WHERE pid=$intId OR id=$intId")
										 ->limit(1)
										 ->executeUncached();
									 
		$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->type_class]['class'];
		
		if (!$this->classFileExists($strClass))
		{
			return null;
		}
									
		$objProduct = new $strClass($objProductData->row());
		
		$objProduct->reader_jumpTo = $this->iso_reader_jumpTo ? $this->iso_reader_jumpTo : $objPage->id;
			
		return $objProduct;
	}
	
	
	/**
	 * Shortcut for a single product by alias (from url?)
	 */
	protected function getProductByAlias($strAlias)
	{
		global $objPage;
		
		$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS type_class FROM tl_iso_products WHERE pid=0 AND alias=?")
										 ->limit(1)
										 ->executeUncached($strAlias);
									 
		$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->type_class]['class'];
		
		if (!$this->classFileExists($strClass))
		{
			return null;
		}
									
		$objProduct = new $strClass($objProductData->row());
		
		$objProduct->reader_jumpTo = $this->iso_reader_jumpTo ? $this->iso_reader_jumpTo : $objPage->id;
			
		return $objProduct;
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
}

