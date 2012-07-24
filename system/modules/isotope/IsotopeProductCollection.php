<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Yanick Witschi <yanick.witschi@certo-net.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class IsotopeProductCollection
 * 
 * Provide methods to handle Isotope product collections.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class IsotopeProductCollection extends Model
{

	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable;

	/**
	 * Define if data should be threaded as "locked", eg. not apply discount rules to product prices
	 * @var boolean
	 */
	protected $blnLocked = false;

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
	protected $Shipping;

	/**
	 * Payment object if payment module is set in product collection
	 * @var object
	 */
	protected $Payment;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_invoice';

	/**
	 * Configuration
	 * @var array
	 */
	protected $arrSettings = array();

	/**
	 * Record has been modified
	 * @var boolean
	 */
	protected $blnModified = false;


	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();

		// Do not use __destruct, because Database object might be destructed first (see http://dev.contao.org/issues/2236)
		if (!$this->blnLocked)
		{
			register_shutdown_function(array($this, 'saveDatabase'));
		}
	}


	/**
	 * Shutdown function to save data if modified
	 */
	public function saveDatabase()
	{
		$this->save();
	}


	/**
	 * Return data
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		if (!isset($this->arrCache[$strKey]))
		{
			if ($this->blnLocked && array_key_exists($strKey, $this->arrData))
			{
				return deserialize($this->arrData[$strKey]);
			}
			elseif ($this->blnLocked && array_key_exists($strKey, $this->arrSettings))
			{
				return deserialize($this->arrSettings[$strKey]);
			}

			switch ($strKey)
			{
				case 'table':
					return $this->strTable;
					break;

				case 'ctable':
					return  $this->ctable;
					break;

				case 'id':
				case 'pid':
					return (int) $this->arrData[$strKey];
					break;

				case 'Shipping':
				case 'Payment':
					return $this->$strKey;
					break;

				case 'hasPayment':
					return (is_object($this->Payment) ? true : false);
					break;

				case 'hasShipping':
					return (is_object($this->Shipping) ? true : false);
					break;

				case 'requiresShipping':
					$this->arrCache[$strKey] = false;
					$arrProducts = $this->getProducts();

					foreach ($arrProducts as $objProduct)
					{
						if (!$objProduct->shipping_exempt)
						{
							$this->arrCache[$strKey] = true;
						}
					}
					break;

				case 'requiresPayment':
					return $this->grandTotal > 0 ? true : false;
					break;

				case 'shippingTotal':
					return $this->hasShipping ? (float) $this->Shipping->price : 0.00;
					break;

				case 'items':
					$this->arrCache[$strKey] = $this->Database->execute("SELECT SUM(product_quantity) AS items FROM {$this->ctable} WHERE pid={$this->id}")->items;
					break;

				case 'products':
					$this->arrCache[$strKey] = $this->Database->execute("SELECT COUNT(*) AS items FROM {$this->ctable} WHERE pid={$this->id}")->items;
					break;

				case 'lastAdded':
					// getProducts() will set the cache key/value.
					// Only if the function has never been called, this will be triggered
					$this->getProducts('', true);
					break;

				case 'subTotal':
					$fltTotal = 0;
					$arrProducts = $this->getProducts();

					foreach ($arrProducts as $objProduct)
					{
						$fltTotal += (float) $objProduct->total_price;
					}

					$this->arrCache[$strKey] = $fltTotal;
					break;
				
				case 'taxFreeSubTotal':
					$fltTotal = 0;
					$arrProducts = $this->getProducts();

					foreach ($arrProducts as $objProduct)
					{
						$fltTotal += (float) $objProduct->tax_free_total_price;
					}

					$this->arrCache[$strKey] = $fltTotal;
					break;

				case 'taxTotal':
					$this->import('Isotope');
					$intTaxTotal = 0;
					$arrSurcharges = $this->getSurcharges();

					foreach ($arrSurcharges as $arrSurcharge)
					{
						if ($arrSurcharge['add'])
						{
							$intTaxTotal += $arrSurcharge['total_price'];
						}
					}

					$this->arrCache[$strKey] = $intTaxTotal;
					break;

				case 'grandTotal':
					$this->import('Isotope');
					$fltTotal = $this->subTotal;
					$arrSurcharges = $this->getSurcharges();

					foreach ($arrSurcharges as $arrSurcharge)
					{
						if ($arrSurcharge['add'] !== false)
						{
							$fltTotal += $arrSurcharge['total_price'];
						}
					}

					$this->arrCache[$strKey] = $fltTotal > 0 ? $this->Isotope->roundPrice($fltTotal) : 0;
					break;

				default:
					if (array_key_exists($strKey, $this->arrData))
					{
						return deserialize($this->arrData[$strKey]);
					}
					else
					{
						return deserialize($this->arrSettings[$strKey]);
					}
					break;
			}
		}

		return $this->arrCache[$strKey];
	}


	/**
	 * Set data
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrCache = array();

		if ($strKey == 'Shipping' || $strKey == 'Payment')
		{
			$this->$strKey = $varValue;
			return;
		}
		elseif ($strKey == 'modified')
		{
			$this->blnModified = (bool) $varValue;
			$this->arrCache = array();
			$this->arrProducts = null;
		}

		// If there is a database field for that key, we store it there
		if (array_key_exists($strKey, $this->arrData) || $this->Database->fieldExists($strKey, $this->strTable))
		{
			$this->arrData[$strKey] = $varValue;
			$this->arrCache = array();
			$this->blnModified = true;
		}

		// We dont want $this->import() objects to be in arrSettings
		elseif (is_object($varValue))
		{
			$this->$strKey = $varValue;
		}

		// Everything else goes into arrSettings and is serialized
		else
		{
			if ($varValue === null)
			{
				unset($this->arrSettings[$strKey]);
			}
			else
			{
				$this->arrSettings[$strKey] = $varValue;
			}

			$this->arrCache = array();
			$this->blnModified = true;
		}
	}


	/**
	 * Check whether a property is set
	 * @param string
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		if (isset($this->arrData[$strKey]) || isset($this->arrSettings[$strKey]))
		{
			return true;
		}

		return false;
	}


	/**
	 * Load settings from database field
	 * @param string
	 * @param mixed
	 * @return boolean
	 */
	public function findBy($strRefField, $varRefId)
	{
		if (parent::findBy($strRefField, $varRefId))
		{
			$this->arrSettings = deserialize($this->arrData['settings'], true);
			return true;
		}

		return false;
	}


	/**
	 * Load settings from database field
	 * @param object
	 * @param string
	 * @param string
	 */
	public function setFromRow(Database_Result $resResult, $strTable, $strRefField)
	{
		parent::setFromRow($resResult, $strTable, $strRefField);
		$this->arrSettings = deserialize($this->arrData['settings'], true);
	}


	/**
	 * Update database with latest product prices and store settings
	 * @param boolean
	 * @return integer
	 */
	public function save($blnForceInsert=false)
	{
		if ($this->blnModified)
		{
			$this->arrData['tstamp'] = time();
			$this->arrData['settings'] = serialize($this->arrSettings);
		}

		$arrProducts = $this->getProducts();

		if (is_array($arrProducts) && count($arrProducts))
		{
			foreach ($arrProducts as $objProduct)
			{
				$this->Database->prepare("UPDATE {$this->ctable} SET price=? WHERE id=?")->execute($objProduct->price, $objProduct->cart_id);
			}
		}

		// HOOK for adding additional functionality when saving
		if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && is_array($GLOBALS['ISO_HOOKS']['saveCollection']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($this);
			}
		}

		if ($this->blnRecordExists && $this->blnModified && !$blnForceInsert)
		{
			return parent::save($blnForceInsert);
		}
		elseif ((!$this->blnRecordExists && $this->blnModified) || $blnForceInsert)
		{
			$this->findBy('id', parent::save($blnForceInsert));
			return $this->id;
		}
	}


	/**
	 * Also delete child table records when dropping this collection
	 * @return integer
	 */
	public function delete()
	{
		// HOOK for adding additional functionality when deleting a collection
		if (isset($GLOBALS['ISO_HOOKS']['deleteCollection']) && is_array($GLOBALS['ISO_HOOKS']['deleteCollection']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['deleteCollection'] as $callback)
			{
				$this->import($callback[0]);
				$blnRemove = $this->$callback[0]->$callback[1]($this);

				if ($blnRemove === false)
				{
					return 0;
				}
			}
		}

		$intAffectedRows = parent::delete();

		if ($intAffectedRows > 0)
		{
			$this->Database->prepare("DELETE FROM " . $this->ctable . " WHERE pid=?")->execute($this->id);
		}

		$this->arrCache = array();
		$this->arrProducts = null;

		return $intAffectedRows;
	}


	/**
	 * Fetch products from database
	 * @param string
	 * @param boolean
	 * @return array
	 */
	public function getProducts($strTemplate='', $blnNoCache=false)
	{
		if (!is_array($this->arrProducts) || $blnNoCache)
		{
			$this->arrProducts = array();
			$this->arrCache['lastAdded'] = 0;
			$lastAdded = 0;

			$objItems = $this->Database->prepare("SELECT * FROM " . $this->ctable . " WHERE pid=? ORDER by id")->executeUncached($this->id);

			while ($objItems->next())
			{
				$objProductData = $this->Database->execute("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS product_class FROM tl_iso_products WHERE id={$objItems->product_id}");
				$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];

				if ($objProductData->numRows && $strClass != '')
				{
					try
					{
						$arrData = $this->blnLocked ? array_merge($objProductData->row(), array('sku'=>$objItems->product_sku, 'name'=>$objItems->product_name, 'price'=>$objItems->price)) : $objProductData->row();
						$objProduct = new $strClass($arrData, deserialize($objItems->product_options), $this->blnLocked);
					}
					catch (Exception $e)
					{
						$objProduct = new IsotopeProduct(array('id'=>$objItems->product_id, 'sku'=>$objItems->product_sku, 'name'=>$objItems->product_name, 'price'=>$objItems->price), deserialize($objItems->product_options), $this->blnLocked);
					}
				}
				else
				{
					$objProduct = new IsotopeProduct(array('id'=>$objItems->product_id, 'sku'=>$objItems->product_sku, 'name'=>$objItems->product_name, 'price'=>$objItems->price), deserialize($objItems->product_options), $this->blnLocked);
				}

				// Remove product from collection if it is no longer available
				if (!$objProduct->available)
				{
					$objProduct->cart_id = $objItems->id;
					$this->deleteProduct($objProduct);
					continue;
				}

				$objProduct->quantity_requested = $objItems->product_quantity;
				$objProduct->cart_id = $objItems->id;
				$objProduct->tax_id = $objItems->tax_id;
				$objProduct->reader_jumpTo_Override = $objItems->href_reader;

				if ($objItems->tstamp > $lastAdded)
				{
					$this->arrCache['lastAdded'] = $objItems->id;
					$lastAdded = $objItems->tstamp;
				}

				$this->arrProducts[] = $objProduct;
			}
		}

		if (strlen($strTemplate))
		{
			$this->import('Isotope');
			$objTemplate = new IsotopeTemplate($strTemplate);
			
			$objTemplate->products = $this->arrProducts;
			$objTemplate->surcharges = IsotopeFrontend::formatSurcharges($this->getSurcharges());
			$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
			$objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($this->subTotal, false);
			$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
			$objTemplate->grandTotalPrice = $this->Isotope->formatPriceWithCurrency($this->grandTotal, false);

			return $objTemplate->parse();
		}

		return $this->arrProducts;
	}


	/**
	 * Add a product to the collection
	 * @param object The product object
	 * @param integer How many products to add
	 * @return integer ID of database record added/updated
	 */
	public function addProduct(IsotopeProduct $objProduct, $intQuantity)
	{
		// HOOK for adding additional functionality when adding product to collection
		if (isset($GLOBALS['ISO_HOOKS']['addProductToCollection']) && is_array($GLOBALS['ISO_HOOKS']['addProductToCollection']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['addProductToCollection'] as $callback)
			{
				$this->import($callback[0]);
				$intQuantity = $this->$callback[0]->$callback[1]($objProduct, $intQuantity, $this);
			}
		}

		if ($intQuantity == 0)
		{
			return false;
		}

		$time = time();
		$this->modified = true;

		// Make sure collection is in DB before adding product
		if (!$this->blnRecordExists)
		{
			$this->save();
		}

		$objItem = $this->Database->prepare("SELECT * FROM {$this->ctable} WHERE pid={$this->id} AND product_id={$objProduct->id} AND product_options=?")->limit(1)->execute(serialize($objProduct->getOptions(true)));

		if ($objItem->numRows)
		{
			$this->Database->query("UPDATE {$this->ctable} SET tstamp=$time, product_quantity=(product_quantity+$intQuantity) WHERE id={$objItem->id}");
			return $objItem->id;
		}
		else
		{
			$arrSet = array
			(
				'pid'				=> $this->id,
				'tstamp'			=> $time,
				'product_id'		=> (int) $objProduct->id,
				'product_sku'		=> (string) $objProduct->sku,
				'product_name'		=> (string) $objProduct->name,
				'product_options'	=> $objProduct->getOptions(true),
				'product_quantity'	=> (int) $intQuantity,
				'price'				=> $objProduct->price,
			);

			if ($this->Database->fieldExists('href_reader', $this->ctable))
			{
				$arrSet['href_reader'] = $objProduct->href_reader;
			}

			$intInsertId = $this->Database->prepare("INSERT INTO {$this->ctable} %s")->set($arrSet)->executeUncached()->insertId;
			return $intInsertId;
		}
	}


	/**
	 * update a product in the collection
	 * @param object The product object
	 * @param array The property(ies) to adjust
	 * @return integer ID of database record added/updated
	 */
	public function updateProduct(IsotopeProduct $objProduct, $arrSet)
	{
		if (!$objProduct->cart_id)
		{
			return false;
		}

		// HOOK for adding additional functionality when updating a product in the collection
		if (isset($GLOBALS['ISO_HOOKS']['updateProductInCollection']) && is_array($GLOBALS['ISO_HOOKS']['updateProductInCollection']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['updateProductInCollection'] as $callback)
			{
				$this->import($callback[0]);
				$arrSet = $this->$callback[0]->$callback[1]($objProduct, $arrSet, $this);

				if (is_array($arrSet) && !count($arrSet))
				{
					return false;
				}
			}
		}

		// Quantity set to 0, delete product
		if (isset($arrSet['product_quantity']) && $arrSet['product_quantity'] == 0)
		{
			return $this->deleteProduct($objProduct);
		}

		// Modify timestamp when updating a product
		$arrSet['tstamp'] = time();

		$intAffectedRows = $this->Database->prepare("UPDATE {$this->ctable} %s WHERE id={$objProduct->cart_id}")
										  ->set($arrSet)
										  ->executeUncached()
										  ->affectedRows;

		if ($intAffectedRows > 0)
		{
			$this->modified = true;
			return true;
		}

		return false;
	}


	/**
	 * Delete a product in the collection
	 * @param object
	 * @return boolean
	 */
	public function deleteProduct(IsotopeProduct $objProduct)
	{
		if (!$objProduct->cart_id)
		{
			return false;
		}

		// HOOK for adding additional functionality when a product is removed from the collection
		if (isset($GLOBALS['ISO_HOOKS']['deleteProductFromCollection']) && is_array($GLOBALS['ISO_HOOKS']['deleteProductFromCollection']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['deleteProductFromCollection'] as $callback)
			{
				$this->import($callback[0]);
				$blnRemove = $this->$callback[0]->$callback[1]($objProduct, $this);

				if ($blnRemove === false)
				{
					return false;
				}
			}
		}

		$this->modified = true;
		$this->Database->query("DELETE FROM {$this->ctable} WHERE id={$objProduct->cart_id}");
		return true;
	}


	/**
	 * Transfer products from another collection to this one (e.g. Cart to Order)
	 * @param object
	 * @param boolean
	 * @return array
	 */
	public function transferFromCollection(IsotopeProductCollection $objCollection, $blnDuplicate=true)
	{
		if (!$this->blnRecordExists)
		{
			$this->save(true);
		}

		// Make sure database table has the latest prices
//		$objCollection->save();

		$time = time();
		$arrIds = array();
	 	$objOldItems = $this->Database->execute("SELECT * FROM {$objCollection->ctable} WHERE pid={$objCollection->id}");

		while ($objOldItems->next())
		{
			$blnTransfer = true;
			$objNewItems = $this->Database->prepare("SELECT * FROM {$this->ctable} WHERE pid={$this->id} AND product_id={$objOldItems->product_id} AND product_options=?")->execute($objOldItems->product_options);

			// HOOK for adding additional functionality when adding product to collection
			if (isset($GLOBALS['ISO_HOOKS']['transferCollection']) && is_array($GLOBALS['ISO_HOOKS']['transferCollection']))
			{
				foreach ($GLOBALS['ISO_HOOKS']['transferCollection'] as $callback)
				{
					$this->import($callback[0]);
					$blnTransfer = $this->$callback[0]->$callback[1]($objOldItems, $objNewItems, $objCollection, $this, $blnTransfer);
				}
			}

			if (!$blnTransfer)
			{
				continue;
			}

			// Product exists in target table. Increase amount.
			if ($objNewItems->numRows)
			{
				$this->Database->query("UPDATE {$this->ctable} SET tstamp=$time, product_quantity=(product_quantity+{$objOldItems->product_quantity}) WHERE id={$objNewItems->id}");
				$arrIds[$objOldItems->id] = $objNewItems->id;
			}

			// Product does not exist in this collection, we don't duplicate and are on the same table. Simply change parent id.
			elseif (!$objNewItems->numRows && !$blnDuplicate && $this->ctable == $objCollection->ctable)
			{
				$this->Database->query("UPDATE {$this->ctable} SET tstamp=$time, pid={$this->id} WHERE id={$objOldItems->id}");
				$arrIds[$objOldItems->id] = $objOldItems->id;
			}

			// Duplicate all existing rows to target table
			else
			{
				$arrSet = array('pid'=>$this->id, 'tstamp'=>$time);

				foreach ($objOldItems->row() as $k => $v)
				{
					if (in_array($k, array('id', 'pid', 'tstamp')))
					{
						continue;
					}

					if ($this->Database->fieldExists($k, $this->ctable))
					{
						$arrSet[$k] = $v;
					}
				}

				$arrIds[$objOldItems->id] = $this->Database->prepare("INSERT INTO {$this->ctable} %s")->set($arrSet)->executeUncached()->insertId;
			}
		}

		if (!empty($arrIds))
		{
			$this->modified = true;
		}
		
		// HOOK for adding additional functionality when adding product to collection
		if (isset($GLOBALS['ISO_HOOKS']['transferredCollection']) && is_array($GLOBALS['ISO_HOOKS']['transferredCollection']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['transferredCollection'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($objCollection, $this, $arrIds);
			}
		}

		return $arrIds;
	}


	/**
	 * Calculate the weight of all products in the cart in a specific weight unit
	 * @param string
	 * @return mixed
	 */
	public function getShippingWeight($unit)
	{
		$this->import('Isotope');
		$arrWeights = array();
		$arrProducts = $this->getProducts();

		foreach ($arrProducts as $objProduct)
		{
			$arrWeight = deserialize($objProduct->shipping_weight, true);
			$arrWeight['value'] = $objProduct->quantity_requested * floatval($arrWeight['value']);

			$arrWeights[] = $arrWeight;
		}

		return $this->Isotope->calculateWeight($arrWeights, $unit);
	}


	/**
	 * Must be implemented by child class
	 */
	abstract public function getSurcharges();


	/**
	 * Generate the collection using a template. Useful for PDF output
	 * @param string
	 * @param boolean
	 * @return string
	 */
	public function generate($strTemplate=null, $blnResetConfig=true)
	{
		if ($strTemplate)
		{
			$this->strTemplate = $strTemplate;
		}

		$this->import('Isotope');

		// Set global config to this collection (if available)
		if ($this->config_id > 0)
		{
			$this->Isotope->overrideConfig($this->config_id);
		}

		$objTemplate = new BackendTemplate($this->strTemplate);
		$objTemplate->setData($this->arrData);
		$objTemplate->logoImage = '';

		if ($this->Isotope->Config->invoiceLogo != '' && is_file(TL_ROOT . '/' . $this->Isotope->Config->invoiceLogo))
		{
		
			$objTemplate->logoImage = '<img src="' . $this->Environment->base . '/' . $this->Isotope->Config->invoiceLogo . '" alt="" />';
		}

		$objTemplate->invoiceTitle = $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] . ' ' . $this->order_id . ' – ' . date($GLOBALS['TL_CONFIG']['datimFormat'], $this->date);

		$arrItems = array();
		$arrProducts = $this->getProducts();

		foreach ($arrProducts as $objProduct)
		{
			$arrItems[] = array
			(
				'raw'				=> $objProduct->getData(),
				'product_options' 	=> $objProduct->getOptions(),
				'name'				=> $objProduct->name,
				'quantity'			=> $objProduct->quantity_requested,
				'price'				=> $objProduct->formatted_price,
				'total'				=> $objProduct->formatted_total_price,
				'tax_id'			=> $objProduct->tax_id,
			);
		}
		
		$objTemplate->config = $this->Isotope->Config->getData(); 	
		$objTemplate->info = deserialize($this->checkout_info);
		$objTemplate->items = $arrItems;
		$objTemplate->raw = $this->arrData;
		$objTemplate->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $this->date);
		$objTemplate->time = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $this->date);
		$objTemplate->datim = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $this->date);
		$objTemplate->datimLabel = $GLOBALS['TL_LANG']['MSC']['datimLabel'];
		$objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($this->subTotal);
		$objTemplate->grandTotal = $this->Isotope->formatPriceWithCurrency($this->grandTotal);
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];

		$objTemplate->surcharges = IsotopeFrontend::formatSurcharges($this->getSurcharges());
		$objTemplate->billing_label = $GLOBALS['TL_LANG']['ISO']['billing_address'];
		$objTemplate->billing_address = $this->Isotope->generateAddressString(deserialize($this->billing_address), $this->Isotope->Config->billing_fields);

		if (strlen($this->shipping_method))
		{
			$arrShippingAddress = deserialize($this->shipping_address);

			if (!is_array($arrShippingAddress) || $arrShippingAddress['id'] == -1)
			{
				$objTemplate->has_shipping = false;
				$objTemplate->billing_label = $GLOBALS['TL_LANG']['ISO']['billing_shipping_address'];
			}
			else
			{
				$objTemplate->has_shipping = true;
				$objTemplate->shipping_label = $GLOBALS['TL_LANG']['ISO']['shipping_address'];
				$objTemplate->shipping_address = $this->Isotope->generateAddressString($arrShippingAddress, $this->Isotope->Config->shipping_fields);
			}
		}

		$strArticle = $this->Isotope->replaceInsertTags($objTemplate->parse());
		$strArticle = html_entity_decode($strArticle, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
		$strArticle = $this->Isotope->convertRelativeUrls($strArticle, '', true);

		// Remove form elements and JavaScript links
		$arrSearch = array
		(
			'@<form.*</form>@Us',
			'@<a [^>]*href="[^"]*javascript:[^>]+>.*</a>@Us'
		);

		$strArticle = preg_replace($arrSearch, '', $strArticle);

		// Handle line breaks in preformatted text
		$strArticle = preg_replace_callback('@(<pre.*</pre>)@Us', 'nl2br_callback', $strArticle);

		// Default PDF export using TCPDF
		$arrSearch = array
		(
			'@<span style="text-decoration: ?underline;?">(.*)</span>@Us',
			'@(<img[^>]+>)@',
			'@(<div[^>]+block[^>]+>)@',
			'@[\n\r\t]+@',
			'@<br /><div class="mod_article@',
			'@href="([^"]+)(pdf=[0-9]*(&|&amp;)?)([^"]*)"@'
		);

		$arrReplace = array
		(
			'<u>$1</u>',
			'<br />$1',
			'<br />$1',
			' ',
			'<div class="mod_article',
			'href="$1$4"'
		);

		$strArticle = preg_replace($arrSearch, $arrReplace, $strArticle);

		// Set config back to default
		if ($blnResetConfig)
		{
			$this->Isotope->resetConfig(true);
		}

		return $strArticle;
	}


	/**
	 * Generate a PDF file and optionally send it to the browser
	 * @param string
	 * @param object
	 * @param boolean
	 */
	public function generatePDF($strTemplate=null, $pdf=null, $blnOutput=true)
	{
		if (!is_object($pdf))
		{
			// TCPDF configuration
			$l['a_meta_dir'] = 'ltr';
			$l['a_meta_charset'] = $GLOBALS['TL_CONFIG']['characterSet'];
			$l['a_meta_language'] = $GLOBALS['TL_LANGUAGE'];
			$l['w_page'] = 'page';

			// Include library
			require_once(TL_ROOT . '/system/config/tcpdf.php');
			require_once(TL_ROOT . '/plugins/tcpdf/tcpdf.php');

			// Create new PDF document
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

			// Set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor(PDF_AUTHOR);

// @todo $objInvoice is not defined
//			$pdf->SetTitle($objInvoice->title);
//			$pdf->SetSubject($objInvoice->title);
//			$pdf->SetKeywords($objInvoice->keywords);

			// Remove default header/footer
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// Set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

			// Set auto page breaks
			$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

			// Set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			// Set some language-dependent strings
			$pdf->setLanguageArray($l);

			// Initialize document and add a page
			$pdf->AliasNbPages();

			// Set font
			$pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);
		}

		// Start new page
		$pdf->AddPage();

		// Write the HTML content
		$pdf->writeHTML($this->generate($strTemplate, false), true, 0, true, 0);

		if ($blnOutput)
		{
			// Close and output PDF document
			// @todo $strInvoiceTitle is not defined
			$pdf->lastPage();
			$pdf->Output(standardize(ampersand($strInvoiceTitle, false), true) . '.pdf', 'D');

			// Stop script execution
			exit;
		}

		return $pdf;
	}
}

