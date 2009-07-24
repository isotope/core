<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

class Isotope extends Controller
{

	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;
	
	
	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone() {}
	
	
	/**
	 * Prevent direct instantiation (Singleton)
	 */
	protected function __construct()
	{
		parent::__construct();
		
		$this->import('Database');
		$this->import('FrontendUser', 'User');
		$this->import('IsotopeStore', 'Store');
	}
	
	
	/**
	 * Instantiate a database driver object and return it (Factory)
	 *
	 * @return object
	 */
	public static function getInstance()
	{
		if (!is_object(self::$objInstance))
		{
			self::$objInstance = new Isotope();
		}

		return self::$objInstance;
	}


	/**
	 * Format given price according to store settings.
	 * 
	 * @access public
	 * @param float $fltPrice
	 * @return float
	 */
	public function formatPrice($fltPrice)
	{
		$arrFormat = $GLOBALS['ISO_NUM'][$this->Store->currencyFormat];
		
		if (!is_array($arrFormat) || !count($arrFormat) == 3)
			return $fltPrice;
		
		return number_format($fltPrice, $arrFormat[0], $arrFormat[1], $arrFormat[2]);
	}
	
	
	/**
	 * Format given price according to store settings, including currency representation.
	 * 
	 * @access public
	 * @param float $fltPrice
	 * @param bool $blnHtml. (default: false)
	 * @return string
	 */
	public function formatPriceWithCurrency($fltPrice, $blnHtml=false)
	{
		$strPrice = $this->formatPrice($fltPrice);
		
		$strCurrency = $this->Store->currency;
		
		if ($this->Store->currencySymbol && strlen($GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency]))
		{
			$strCurrency = ($blnHtml ? '<span class="currency">' : '') . $GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency] . ($blnHtml ? '</span>' : '');
		}
		else
		{
			$strCurrency = ($this->Store->currencyPosition == 'right' ? ' ' : '') . ($blnHtml ? '<span class="currency">' : '') . $strCurrency . ($blnHtml ? '</span>' : '') . ($this->Store->currencyPosition == 'left' ? ' ' : '');
		}
		
		if ($this->Store->currencyPosition == 'right')
		{
			return $strPrice . $strCurrency;
		}
		
		return $strCurrency . $strPrice;
	}
	
	
	/**
	 * Auto-Login new user and copy address to address book.
	 * 
	 * @todo allow user to choose auto-activation (in store config?)
	 * @access public
	 * @param int $intId
	 * @param array $arrData
	 * @return void
	 */
	public function createNewUser($intId, $arrData)
	{
		$arrSet = array
		(
			'pid'				=> $intId,
			'tstamp'			=> $arrData['tstamp'],
			'firstname'			=> $arrData['firstname'],
			'lastname'			=> $arrData['lastname'],
			'company'			=> $arrData['company'],
			'street'			=> $arrData['street'],
			'postal'			=> $arrData['postal'],
			'city'				=> $arrData['city'],
			'state'				=> $arrData['state'],
			'country'			=> $arrData['country'],
			'phone'				=> $arrData['phone'],
			'isDefaultBilling'	=> '1',
			'isDefaultShipping' => '1',
		
		);
	
		
		$this->Database->prepare('INSERT INTO tl_address_book %s')
					   ->set($arrSet)
					   ->execute();
					   
		$this->Database->prepare("UPDATE tl_member SET disable=0 WHERE id=?")->execute($intId);
	}
	
	
	
	
	
	
	
	
	public function getProductData($arrAggregateSetData, $arrFieldNames, $strOrderByField)
	{					
		$strFieldList = join(',', $arrFieldNames);
		$arrProductsAndTables = array();

		foreach($arrAggregateSetData as $data)
		{
			$arrProductsAndTables[$data['storeTable']][] = array($data['product_id'], $data['quantity_requested']); //Allows us to cycle thru the correct table and product ids collections.
			
			//The productID list for this storetable, used to build the IN clause for the product gathering.
			$arrProductIds[$data['storeTable']][] = $data['product_id'];
			
			$arrProductExtraFields[$data['storeTable']][$data['product_id']]['cart_item_id'] = $data['id'];
			//This is used to gather extra fields for a given product by store table.
			$arrProductExtraFields[$data['storeTable']][$data['product_id']]['attribute_set_id'] = $data['attribute_set_id'];
			
			$arrProductExtraFields[$data['storeTable']][$data['product_id']]['source_cart_id'] = $data['source_cart_id'];
			
			$arrProductExtraFields[$data['storeTable']][$data['product_id']]['price'] = $data['price'];
			
			//Aggregate full product quantity all into one product line item for now.
			if($arrProductExtraFields[$data['storeTable']][$data['product_id']]['quantity_requested']<1)
			{
				$arrProductExtraFields[$data['storeTable']][$data['product_id']]['quantity_requested'] = $data['quantity_requested'];
			}else{
				$arrProductExtraFields[$data['storeTable']][$data['product_id']]['quantity_requested'] += $data['quantity_requested'];
			}
			
			if(strlen($data['product_options']))
			{	
				$arrProductExtraFields[$data['storeTable']][$data['product_id']]['product_options'] = deserialize($data['product_options']);
			}
		}
						
		$arrTotalProductsInCart = array();
					
		foreach($arrProductsAndTables as $k=>$v)
		{
							
			$strCurrentProductList = join(',', $arrProductIds[$k]);
						
			$objProducts = $this->Database->prepare("SELECT id, " . $strFieldList . " FROM " . $k . " WHERE id IN(" . $strCurrentProductList . ") ORDER BY " . $strOrderByField . " ASC")
										  ->execute();
			
			if($objProducts->numRows < 1)
			{
				return array();
			}
			
			$arrProductsInCart = $objProducts->fetchAllAssoc();
						
			foreach($arrProductsInCart as $product)
			{
				$arrProducts[$product['id']]['id'] = $product['id'];
				
				foreach($arrFieldNames as $field)
				{
					if (($field == 'main_image') && !strlen($product[$field]))
					{
						$this->import('MediaManagement');
						$product[$field] = $this->MediaManagement->getFirstOrdinalImage('assets/%s/%s/images/gallery_thumbnail_images', $product['alias']);
					}
					
					$arrProducts[$product['id']][$field] = $product[$field];
				}
				
				$arrProducts[$product['id']]['attribute_set_id'] = $arrProductExtraFields[$k][$product['id']]['attribute_set_id'];
				$arrProducts[$product['id']]['source_cart_id'] = $arrProductExtraFields[$k][$product['id']]['source_cart_id'];
				$arrProducts[$product['id']]['quantity_requested'] = $arrProductExtraFields[$k][$product['id']]['quantity_requested'];
				$arrProducts[$product['id']]['product_options'] = $arrProductExtraFields[$k][$product['id']]['product_options'];
				$arrProducts[$product['id']]['cart_item_id'] = $arrProductExtraFields[$k][$product['id']]['cart_item_id'];
				$arrProducts[$product['id']]['price'] = $arrProductExtraFields[$k][$product['id']]['price'];
			}
	
								
			$arrTotalProductsInCart = array_merge($arrTotalProductsInCart, $arrProducts);
		}
		
		//Retrieve current session data, only if a new product has been added or else the cart updated in some way, and reassign the cart product data
//		$session = $this->Session->getData();
		
		//clean old cart data
//		unset($session['isotope']['cart_data']);
		
		//set new cart data
//		$session['isotope']['cart_data'] = $arrTotalProductsInCart;
		
		
//		$session['isotope']['cart_id'] = $this->userCartExists($this->strUserId);
		
		
//		$this->Session->setData($session);
				
		return $arrTotalProductsInCart;
	}
	
	public function getProductPrice($intProductId, $strTable)
	{
		$objPrice = $this->Database->prepare("SELECT price FROM " . $strTable . " WHERE id=?")
										->limit(1)
										->execute($intProductId);
		
		if($objPrice->numRows < 1)
		{
			return false;
		}
		
		return $objPrice->price;
	
	}
	
	public function getAddress($strStep = 'billing')
	{
		if ($_SESSION['FORM_DATA'][$strStep.'_address'] && !isset($_SESSION['FORM_DATA']['billing_address']))
			return false;
			
		$intAddressId = $_SESSION['FORM_DATA'][$strStep.'_address'];
		
		// Take billing address
		if ($intAddressId == -1)
		{
			$intAddressId = $_SESSION['FORM_DATA']['billing_address'];
			$strStep = 'billing';
		}
		
		if ($intAddressId == 0)
		{
			$arrAddress = array
			(
				'company'		=> $_SESSION['FORM_DATA'][$strStep . '_information_company'],
				'firstname'		=> $_SESSION['FORM_DATA'][$strStep . '_information_firstname'],
				'lastname'		=> $_SESSION['FORM_DATA'][$strStep . '_information_lastname'],
				'street'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street'],
				'street_2'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street_2'],
				'street_3'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street_3'],
				'city'			=> $_SESSION['FORM_DATA'][$strStep . '_information_city'],
				'state'			=> $_SESSION['FORM_DATA'][$strStep . '_information_state'],
				'postal'		=> $_SESSION['FORM_DATA'][$strStep . '_information_postal'],
				'country'		=> $_SESSION['FORM_DATA'][$strStep . '_information_country'],
			);
			
			if ($strStep == 'billing')
			{
				$arrAddress['email'] = (strlen($_SESSION['FORM_DATA'][$strStep . '_information_email']) ? $_SESSION['FORM_DATA'][$strStep . '_information_email'] : $this->User->email);
				$arrAddress['phone'] = (strlen($_SESSION['FORM_DATA'][$strStep . '_information_phone']) ? $_SESSION['FORM_DATA'][$strStep . '_information_phone'] : $this->User->phone);
			}
		}
		else
		{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=?")
												->limit(1)
												->execute($intAddressId);
		
			if($objAddress->numRows < 1)
			{
				return $GLOBALS['TL_LANG']['MSC']['ERR']['specifyBillingAddress'];
			}
			
			$arrAddress = $objAddress->fetchAssoc();
			$arrAddress['email'] = $this->User->email;
			$arrAddress['phone'] = $this->User->phone;
		}
				
		return $arrAddress;
	}

	
	
	/**
	 * Send an email using the isotope e-mail templates.
	 * 
	 * @access public
	 * @param int $intId
	 * @param string $strRecipient
	 * @param string $strLanguage
	 * @param array $arrData
	 * @return void
	 */
	public function sendMail($intId, $strRecipient, $strLanguage, $arrData)
	{
		$objMail = $this->Database->prepare("SELECT * FROM tl_iso_mail m LEFT OUTER JOIN tl_iso_mail_content c ON m.id=c.pid WHERE m.id=? AND (c.language=? OR fallback='1') ORDER BY fallback DESC")->limit(1)->execute($intId, $strLanguage);
		
		if (!$objMail->numRows)
		{
			$this->log(sprintf('E-mail template ID %s for language %s not found', $intId, strtoupper($strLanguage)), 'Isotope sendMail()', TL_ERROR);
			return;
		}
		
		$objEmail = new Email();
		$objEmail->from = $objMail->sender;
		$objEmail->fromName = $objMail->senderName;
		$objEmail->subject = $this->parseSimpleTokens($objMail->subject, $arrData);
		$objEmail->text = $this->parseSimpleTokens($objMail->text, $arrData);
		
		if (!$objMail->textOnly && strlen($objMail->html))
		{
			// Add HTML content
			if (!$objMail->sendText)
			{
				// Get mail template
				$objTemplate = new FrontendTemplate((strlen($objMail->template) ? $objMail->template : 'mail_default'));
	
				$objTemplate->title = $objMail->subject;
				$objTemplate->body = $this->parseSimpleTokens($objMail->html, $arrData);
				$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
				$objTemplate->css = $css;
	
				// Parse template
				$objEmail->html = $objTemplate->parse();
				$objEmail->imageDir = TL_ROOT . '/';
			}
		}
		
		if (strlen($objMail->cc))
		{
			$objEmail->sendCc($objMail->cc);
		}
		
		if (strlen($objMail->bcc))
		{
			$objEmail->sendBcc($objMail->bcc);
		}
		
		$objEmail->sendTo($strRecipient);
	}
	
	public function applyRules($fltProductBasePrice, $intProductId, $storeTable)
	{
		/*
		if($this->Database->fieldExists('is_sale_item',$storeTable))
		{
				
			$objIsSaleItem = $this->Database->prepare("SELECT is_sale_item FROM " . $storeTable . " WHERE id=?")
											->limit(1)
											->execute($intProductId);
											
			if($objIsSaleItem->numRows < 1)
			{
				$isSaleItem = false;
			}
			
			$isSaleItem = $objIsSaleItem->is_sale_item=='1' ? true : false;
		}else{
			$isSaleItem = false;
		}
						
		if(in_array(2, $this->User->groups) && !$isSaleItem)	//this is where rules will later be loaded
		{
			$fltAdjustedPrice = $fltProductBasePrice - ($fltProductBasePrice * .1);
		}else{
			$fltAdjustedPrice = $fltProductBasePrice;
		}
		
		return $fltAdjustedPrice;*/
		
		return $fltProductBasePrice;
	
	}
	
	public function getStoreTableByAggregateSetId($intAsetId)
	{
		$objAggregateSetId = $this->Database->prepare("SELECT storeTable FROM tl_cap_aggregate WHERE id=?")
											->limit(1)
											->execute($intAsetId);
		if($objAggregateSetId->numRows < 1)
		{
			return false;
		}	
		
		return $objAggregateSetId->storeTable;
	}
	
	public function getStoreTableByAttributeSetId($intAttributeSetId)
	{
		$objAttributeSetId = $this->Database->prepare("SELECT storeTable FROM tl_product_attribute_sets WHERE id=?")
											->limit(1)
											->execute($intAttributeSetId);
		if($objAttributeSetId->numRows < 1)
		{
			return false;
		}	
		
		return $objAttributeSetId->storeTable;
	}
}

