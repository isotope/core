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
 

abstract class IsotopeProductCollection extends Model
{
	
	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable;
	
	/**
	 * Cache all products for speed improvements
	 * @var array
	 */
	protected $arrProducts;
	
	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;
	
	/**
	 * Shipping object if shipping module is set in product collection
	 * @var object
	 */
	public $Shipping;
	
	/**
	 * Payment object if payment module is set in product collection
	 * @var object
	 */
	public $Payment;
	
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	/**
	 * Return data.
	 * 
	 * @access public
	 * @param string $strKey
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'table':
				return $this->strTable;
				break;
				
			case 'ctable':
				return  $this->ctable;
				break;
							
			case 'hasPayment':
				return (is_object($this->Payment) ? true : false);
				break;
			
			case 'hasShipping':
				return (is_object($this->Shipping) ? true : false);
				break;
				
			case 'requiresShipping':
				$arrProducts = $this->getProducts();
				foreach( $arrProducts as $objProduct )
				{
					if (!$objProduct->shipping_exempt)
					{
						return true;
					}
				}
				return false;
				break;
				
			case 'shippingTotal':
				return $this->hasShipping ? (float)$this->Shipping->price : 0.00;
				break;
				
			case 'items':
				return $this->Database->execute("SELECT SUM(product_quantity) AS items FROM {$this->ctable} i LEFT OUTER JOIN {$this->strTable} c ON i.pid=c.id WHERE i.pid={$this->id}")->items;
				break;
					
			case 'products':
				return $this->Database->execute("SELECT COUNT(*) AS items FROM {$this->ctable} i LEFT OUTER JOIN {$this->strTable} c ON i.pid=c.id WHERE i.pid={$this->id}")->items;
				break;
				
			case 'subTotal':	
				$fltTotal = 0;
				
				$arrProducts = $this->getProducts();
				foreach( $arrProducts as $objProduct )
				{
					$fltTotal += ((float)$objProduct->price * (int)$objProduct->quantity_requested);
				}
				
				return $fltTotal;
				break;
				
			case 'taxTotal':
				$intTaxTotal = 0;
				
				$arrSurcharges = $this->getSurcharges();
				foreach( $arrSurcharges as $arrSurcharge )
				{
					if ($arrSurcharge['add'])
						$intTaxTotal += $arrSurcharge['total_price'];
				}
				
				return $intTaxTotal;
				break;
				
			case 'grandTotal':
				$fltTotal = $this->subTotal;
				
				$arrSurcharges = $this->getSurcharges();
				foreach( $arrSurcharges as $arrSurcharge )
				{
					if ($arrSurcharge['add'] !== false)
						$fltTotal += $arrSurcharge['total_price'];
				}
				
				return $fltTotal;
				break;
								
			default:
				return parent::__get($strKey);
				break;
		}
	}
	
	
	/**
	 * Also delete child table records when dropping this collection.
	 *
	 * @access public
	 * @return int
	 */
	public function delete()
	{
		// HOOK for adding additional functionality when deleting a collection
		if (isset($GLOBALS['TL_HOOKS']['iso_deleteCollection']) && is_array($GLOBALS['TL_HOOKS']['iso_deleteCollection']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_deleteCollection'] as $callback)
			{
				$this->import($callback[0]);
				$blnRemove = $this->$callback[0]->$callback[1]($this);
				
				if ($blnRemove === false)
					return 0;
			}
		}
		
		$intAffectedRows = parent::delete();
		
		if ($intAffectedRows > 0)
		{
			$this->Database->prepare("DELETE FROM " . $this->ctable . " WHERE pid=?")->execute($this->id);
		}
		
		return $intAffectedRows;
	}
	

	/**
	 * Fetch products from database.
	 * 
	 * @access public
	 * @return array
	 */
	public function getProducts($strTemplate='', $blnNoCache=false)
	{
		if (!is_array($this->arrProducts) || $blnNoCache)
		{
			$this->arrProducts = array();
			$objItems = $this->Database->prepare("SELECT * FROM " . $this->ctable . " WHERE pid=?")->execute($this->id);
	
			while( $objItems->next() )
			{
				$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS product_class FROM tl_iso_products WHERE pid={$objItems->product_id} OR id={$objItems->product_id}")->limit(1)->execute();
								
				$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];
				
				try
				{
					$objProduct = new $strClass($objProductData->row());
				}
				catch (Exception $e)
				{
					$objProduct = new IsotopeProduct(array('id'=>$objItems->product_id, 'sku'=>$objItems->product_sku, 'name'=>$objItems->product_name, 'price'=>$objItems->price));
				}
				
				$objProduct->quantity_requested = $objItems->product_quantity;
				$objProduct->cart_id = $objItems->id;
				$objProduct->reader_jumpTo_Override = $objItems->href_reader;
				
				//!is this correct?
				if($objProduct->price!==$objItems->price)
					$objProduct->price = $objItems->price;
				
				$objProduct->setOptions(deserialize($objItems->product_options, true));
			
				$this->arrProducts[] = $objProduct;
			}
		}
		
		if (isset($GLOBALS['TL_HOOKS']['iso_getProductUpdates']) && is_array($GLOBALS['TL_HOOKS']['iso_getProductUpdates']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_getProductUpdates'] as $callback)
			{				
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($this->arrProducts, $this);
			}
		}
		
		if (strlen($strTemplate))
		{
			$objTemplate = new FrontendTemplate($strTemplate);
			$objTemplate->products = $this->arrProducts;
			return $objTemplate->parse();
		}
				
		return $this->arrProducts;
	}
	
	
	/**
	 * Add a product to the collection
	 *
	 * @access	public
	 * @param	object	The product object
	 * @param	int		How many products to add
	 * @return	int		ID of database record added/updated
	 */
	public function addProduct(IsotopeProduct $objProduct, $intQuantity)
	{
		// HOOK for adding additional functionality when adding product to collection
		if (isset($GLOBALS['TL_HOOKS']['iso_addProductToCollection']) && is_array($GLOBALS['TL_HOOKS']['iso_addProductToCollection']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_addProductToCollection'] as $callback)
			{
				$this->import($callback[0]);
				$intQuantity = $this->$callback[0]->$callback[1]($objProduct, $intQuantity, $this);
			}
		}
		
		if ($intQuantity == 0)
			return false;
		
		$objItem = $this->Database->prepare("SELECT * FROM {$this->ctable} WHERE pid={$this->id} AND product_id={$objProduct->id} AND product_options='".serialize($objProduct->getOptions(true))."'")->limit(1)->execute();
		
		if ($objItem->numRows)
		{
			$this->Database->query("UPDATE {$this->ctable} SET product_quantity=(product_quantity+$intQuantity) WHERE id={$objItem->id}");
			
			return $objItems->id;
		}
		else
		{
			$arrSet = array
			(
				'pid'					=> $this->id,
				'tstamp'				=> time(),
				'product_id'			=> $objProduct->id,
				'product_sku'			=> $objProduct->sku,
				'product_name'			=> $objProduct->name,
				'product_options'		=> $objProduct->getOptions(true),
				'product_quantity'		=> $intQuantity,
				'price'					=> $objProduct->price,
				'href_reader'			=> $objProduct->href_reader
			);
			
			$intInsertId = $this->Database->prepare("INSERT INTO {$this->ctable} %s")->set($arrSet)->executeUncached()->insertId;

			if (isset($GLOBALS['TL_HOOKS']['iso_getProductCollectionInsertId']) && is_array($GLOBALS['TL_HOOKS']['iso_getProductCollectionInsertId']))
			{
				foreach ($GLOBALS['TL_HOOKS']['iso_getProductCollectionInsertId'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($objProduct, $arrSet, $intInsertId, $this);
				}
			}
			
			return $intInsertId;
			
		}
	}
	
	
	/**
	 * update a product in the collection
	 *
	 * @access	public
	 * @param	object	The product object
	 * @param   array The property(ies) to adjust
	 * @return	int		ID of database record added/updated
	 */
	public function updateProduct(IsotopeProduct $objProduct, $arrSet)
	{
		if (!$objProduct->cart_id)
			return false;
			
		// Quantity set to 0, delete product
		if (isset($arrSet['product_quantity']) && $arrSet['product_quantity'] == 0)
		{
			return $this->deleteProduct($objProduct);
		}
		
		// HOOK for adding additional functionality when updating a product in the collection
		if (isset($GLOBALS['TL_HOOKS']['iso_updateProductInCollection']) && is_array($GLOBALS['TL_HOOKS']['iso_updateProductInCollection']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_updateProductInCollection'] as $callback)
			{
				$this->import($callback[0]);
				$arrSet = $this->$callback[0]->$callback[1]($objProduct, $arrSet, $this);
				
				if (is_array($arrSet) && !count($arrSet))
					return false;
			}
		}
		
		$intAffectedRows = $this->Database->prepare("UPDATE {$this->ctable} %s WHERE id={$objProduct->cart_id}")
										  ->set($arrSet)
										  ->executeUncached()
										  ->affectedRows;
		
		if ($intAffectedRows > 0)
			return true;
		
		return false;
	}
	
	
	public function deleteProduct(IsotopeProduct $objProduct)
	{
		if (!$objProduct->cart_id)
			return false;
			
		// HOOK for adding additional functionality when a product is removed from the collection
		if (isset($GLOBALS['TL_HOOKS']['iso_deleteProductFromCollection']) && is_array($GLOBALS['TL_HOOKS']['iso_deleteProductFromCollection']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_deleteProductFromCollection'] as $callback)
			{
				$this->import($callback[0]);
				$blnRemove = $this->$callback[0]->$callback[1]($objProduct, $this);
				
				if ($blnRemove === false)
					return false;
			}
		}
				
		$this->Database->query("DELETE FROM {$this->ctable} WHERE id={$objProduct->cart_id}");
		
		return true;
	}
	
	
	/**
	 * Transfer products from another collection to this one (eg. Cart to Order)
	 */
	//!todo: implement addToCollection (and removeFromCollection) hooks!
	public function transferFromCollection(IsotopeProductCollection $objCollection, $blnDuplicate=true)
	{
		if (!$this->blnRecordExists)
			return array();
			
		$time = time();
		$arrIds = array();
	 	$objOldItems = $this->Database->execute("SELECT * FROM {$objCollection->ctable} WHERE pid={$objCollection->id}");
		
		while( $objOldItems->next() )
		{
			$objNewItems = $this->Database->execute("SELECT * FROM {$this->ctable} WHERE pid={$this->id} AND product_id={$objOldItems->product_id} AND product_options='{$objOldItems->product_options}'");
			
			// Product exists in target table. Increase amount.
			if ($objNewItems->numRows)
			{
				$this->Database->query("UPDATE {$this->ctable} SET tstamp=$time, product_quantity=(product_quantity+{$objOldItems->product_quantity}) WHERE id={$objNewItems->id}");
				$arrIds[] = $objNewItems->id;
			}
			
			// Product does not exist in this collection, we don't duplicate and are on the same table. Simply change parent id.
			elseif (!$objNewItems->numRows && !$blnDuplicate && $this->ctable == $objCollection->ctable)
			{
				$this->Database->query("UPDATE {$this->ctable} SET tstamp=$time, pid={$this->id} WHERE id={$objOldItems->id}");
				$arrIds[] = $objOldItems->id;
			}
			
			// Duplicate all existing rows to target table
			else
			{
				$arrSet = array('pid'=>$this->id, 'tstamp'=>$time);
				
				foreach( $objOldItems->row() as $k=>$v )
				{
					if (in_array($k, array('id', 'pid', 'tstamp')))
						continue;
						
					if ($this->Database->fieldExists($k, $this->ctable))
					{
						$arrSet[$k] = $v;
					}
				}
				
				$arrIds[] = $this->Database->prepare("INSERT INTO {$this->ctable} %s")->set($arrSet)->executeUncached()->insertId;
			}
		}
		
		return $arrIds;
	}
	
	
	/**
	 * Must be implemented by child class
	 */
	abstract public function getSurcharges();
}

