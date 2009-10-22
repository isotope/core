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
		
		
		
		$blnForceDefault = (TL_MODE=='BE' ? true : false);
		
		$this->setStore($blnForceDefault);
		
		
		
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
	 * Set the default store
	 *
	 * @access public
	 * @return void
	 */
	public function setStore($blnForceDefault = false)
	{
		global $objPage;
	
		if($blnForceDefault)
		{
			$_SESSION['isotope']['store_id'] = $this->getDefaultStore();
		}else{	
		
			if(!isset($_SESSION['isotope']['store_id']))
			{
				if($objPage->isotopeStoreConfig)
				{
					//Assign
					$_SESSION['isotope']['store_id'] = $objPage->isotopeStoreConfig;
				}else{
					
					if($objPage->pid<1)
					{
						$_SESSION['isotope']['store_id'] = $this->getDefaultStore();
					}else{
						//Find (recursive look at parents
						$_SESSION['isotope']['store_id'] = $this->getStoreConfigFromParent($objPage->id);
					}
				}
			}
		}
			
		return;
	}
	
	/** 
	 * Get a default store - either one indicated as default in records or else the first record available.
	 *
	 * return integer (store id)
	 */
	public function getDefaultStore()
	{
		$objDefaultStore = $this->Database->prepare("SELECT id, isDefaultStore FROM tl_store")
											  ->execute(1);
											  			
		if($objDefaultStore->numRows < 1)
		{
			if (TL_MODE == 'BE')
			{
				$this->log($GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet'], 'Isotope getDefaultStore', TL_ERROR);
				$this->redirect('typolight/main.php?act=error');
			}
			else
			{
				throw new Exception($GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet']);
			}
		}
		
		while($objDefaultStore->next())
		{
			if($objDefaultStore->isDefaultStore)
			{
				return $objDefaultStore->id;
			}
		}	
		
		$objDefaultStore->first();	//grab the first store in the list if none are set as default
		
		return $objDefaultStore->id;
		
	}
	
	/** 
	 * Manual override of the store
	 * 
	 * @param integer $intStoreId;
	 * @return void
	 */
	public function overrideStore($intStoreId)
    {
	 	$_SESSION['isotope']['store_id'] = $intStoreId;
	  		
	  	return;
	}

	/** 
	 * Recursively look for a store set in a give page.  Continue looking at parent pages until one is found or else
	 * revert to default store otherwise specified.
	 *
	 * @param integer $intPageId
	 * @return integer (store id)
	 */
	private function getStoreConfigFromParent($intPageId)
	{
	
		$objStoreConfiguration = $this->Database->prepare("SELECT pid, isotopeStoreConfig FROM tl_page WHERE id=?")
												->execute($intPageId);
												
		if($objStoreConfig->numRows < 1)
		{
			return $this->getDefaultStore();
		}
		
		if($objStoreConfiguration->isotopeStoreConfig)
		{
			return $objStoreConfiguration->isotopeStoreConfig;
		}
		elseif($objStoreConfiguration->pid<1)
		{
			return $this->getDefaultStore();
		}else{
			return $this->getStoreConfigFromParent($objStoreConfiguration->pid);
		}
	}

	/**
	 * Format given price according to store settings.
	 * 
	 * @access public
	 * @param float $fltPrice
	 * @return float
	 */
	public function formatPrice($fltPrice, $strCurrencyCode)
	{
		if(!$strCurrencyCode)
		{
			throw new Exception($GLOBALS['TL_LANG']['ERR']['missingCurrencyCode']);
		}
		
		$arrFormat = $GLOBALS['ISO_NUM'][$strCurrencyCode];
		
		if (!is_array($arrFormat) || !count($arrFormat) == 3)
			return $fltPrice;
		
		return number_format($fltPrice, $arrFormat[0], $arrFormat[1], $arrFormat[2]);
	}
	
	
	/**
	 * Format given price according to store settings, including currency representation.
	 * 
	 * @access public
	 * @param float $fltPrice
	 * @param string $strCurrencyCode (default: null)
	 * @param bool $blnHtml. (default: false)
	 * @return string
	 */
	public function formatPriceWithCurrency($fltPrice, $strCurrencyCode = null, $blnHtml=false)
	{
		$strCurrency = (strlen($strCurrencyCode) ? $strCurrencyCode : $this->Store->currency);
		
		$strPrice = $this->formatPrice($fltPrice, $strCurrency);
		
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
	
	
	
	
	
	
	
	
	public function getProductData($arrCartItemsData, $arrFieldNames, $strOrderByField)
	{		
		$strFieldList = join(',', $arrFieldNames);
		
		foreach($arrCartItemsData as $configRow)
		{
							
			$objProductData = $this->Database->prepare("SELECT id, " . $strFieldList . " FROM tl_product_data WHERE id=?")
										  ->limit(1)
										  ->execute($configRow['product_id']);
					
			if($objProductData->numRows < 1)
			{
				//DON'T return as it will ignore any valid product ids.  this will allow us to gracefully move on.
				continue;
			}
			
			$arrProductsInCart = $objProductData->fetchAllAssoc();
						
			foreach($arrProductsInCart as $product)
			{
												
				$arrProducts[$configRow['id']]['product_id'] = $configRow['product_id'];	//product_id
				
				//get anything else that might be wanted according to the field list.
				foreach($arrFieldNames as $field)
				{
					if (($field == 'main_image') && !strlen($product[$field]))
					{
						$this->import('MediaManagement');
						$product[$field] = $this->MediaManagement->getFirstOrdinalImage($GLOBALS['TL_CONFIG']['isotope_base_path'] . '/%s/%s/images/gallery_thumbnail_images', $product['alias']);
					}
					
					$arrProducts[$configRow['id']][$field] = $product[$field];
				}
			
				$arrProducts[$configRow['id']]['cart_item_id'] = $configRow['id'];
				$arrProducts[$configRow['id']]['source_cart_id'] = $configRow['pid'];
				$arrProducts[$configRow['id']]['quantity_requested'] = $configRow['quantity_requested'];
				$arrProducts[$configRow['id']]['product_options'] = deserialize($configRow['product_options']);
				$arrProducts[$configRow['id']]['price'] = $configRow['price'];
			}
		}
			
		
		//Retrieve current session data, only if a new product has been added or else the cart updated in some way, and reassign the cart product data
//		$session = $this->Session->getData();
		
		//clean old cart data
//		unset($session['isotope']['cart_data']);
		
		//set new cart data
//		$session['isotope']['cart_data'] = $arrTotalProductsInCart;
		
		
//		$session['isotope']['cart_id'] = $this->userCartExists($this->strUserId);
		
		
//		$this->Session->setData($session);
			
		return $arrProducts;
	}
	
	public function getProductPrice($intProductId)
	{
		$objPrice = $this->Database->prepare("SELECT price FROM tl_product_data WHERE id=?")
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
		//TODO - clean up all getAddress stuff...	
		if($strStep=='shipping' && !FE_USER_LOGGED_IN && $_SESSION['FORM_DATA']['shipping_address']==-1)
		{
			$strStep = 'billing';
		}
				
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

	
	
	public function loadAddressById($intAddressId, $strStep)
    {
        $objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=?")
									->limit(1)
									->execute($intAddressId);
	
		if($objAddress->numRows < 1)
		{
			return $GLOBALS['TL_LANG']['MSC']['ERR']['specifyBillingAddress'];
		}
		
		$arrAddress = $objAddress->fetchAssoc();
		
		$strEmail = (strlen($arrAddress['email']) ? $arrAddress['email'] : $this->User->email);
		$strPhone = (strlen($arrAddress['phone']) ? $arrAddress['phone'] : $this->User->phone);
		
		$_SESSION['FORM_DATA'][$strStep . '_information_company'] = $arrAddress['company'];
		$_SESSION['FORM_DATA'][$strStep . '_information_firstname'] = $arrAddress['firstname'];
		$_SESSION['FORM_DATA'][$strStep . '_information_lastname'] = $arrAddress['lastname'];
		$_SESSION['FORM_DATA'][$strStep . '_information_street'] = $arrAddress['street'];
		$_SESSION['FORM_DATA'][$strStep . '_information_street_2'] = $arrAddress['street_2'];
		$_SESSION['FORM_DATA'][$strStep . '_information_street_3'] = $arrAddress['street_3'];
		$_SESSION['FORM_DATA'][$strStep . '_information_city'] = $arrAddress['city'];
		$_SESSION['FORM_DATA'][$strStep . '_information_state'] = $arrAddress['state'];
		$_SESSION['FORM_DATA'][$strStep . '_information_postal'] = $arrAddress['postal'];
		$_SESSION['FORM_DATA'][$strStep . '_information_country'] = $arrAddress['country'];			
		$_SESSION['FORM_DATA'][$strStep . '_information_email'] = $strEmail;
		$_SESSION['FORM_DATA'][$strStep . '_information_phone'] = $strPhone;

        return true;
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
		
		// Replace insert tags
		$text = $this->parseSimpleTokens($objMail->text, $arrData);
		
		$objEmail->text = $this->replaceInsertTags($text);
		
		$css = '';

		// Add style sheet newsletter.css
		if (!$objNewsletter->sendText && file_exists(TL_ROOT . '/newsletter.css'))
		{
			$buffer = file_get_contents(TL_ROOT . '/newsletter.css');
			$buffer = preg_replace('@/\*\*.*\*/@Us', '', $buffer);

			$css  = '<style type="text/css">' . "\n";
			$css .= trim($buffer) . "\n";
			$css .= '</style>' . "\n";
		}
		
		if (!$objMail->textOnly && strlen($objMail->html))
		{
			// Add HTML content
			if (!$objMail->sendText)
			{
				// Get mail template
				$objTemplate = new FrontendTemplate((strlen($objMail->template) ? $objMail->template : 'mail_default'));
	
				$objTemplate->title = $objMail->subject;
				$html = $this->replaceInsertTags($objMail->html);
				$objTemplate->body = $this->parseSimpleTokens($html, $arrData);
				$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
				$objTemplate->css = $css;
	
				// Parse template
				$objParsedTemplate = $objTemplate->parse();
				// Replace insert tags in the template itself
				$objEmail->html = $this->replaceInsertTags($objParsedTemplate);
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
	
	public function applyRules($fltProductBasePrice, $intProductId)
	{
		
				
		$objData = $this->Database->prepare("SELECT pid FROM tl_product_data WHERE id=?")
										->limit(1)
										->execute($intProductId);
										
		if($objData->numRows < 1)
		{
			$isExcluded = false;
		}
		
		$isExcluded = in_array($objData->pid, $GLOBALS['ISO_RULES']['excludeAttributeSets']);
		
						
		if(in_array(2, $this->User->groups) && !$isExcluded)	//this is where rules will later be loaded
		{
			$fltAdjustedPrice = $fltProductBasePrice - ($fltProductBasePrice * .1);
		}else{
			$fltAdjustedPrice = $fltProductBasePrice;
		}
		
		return $fltAdjustedPrice;
		
		//return $fltProductBasePrice;
	
	}
	
	/** 
	 * Get the next sorting value if it exists for a given table.
	 * 
	 * @access public
	 * @param string $strTable
	 * @return integer;
	 */
	public function getNextSortValue($strTable)
	{
		if($this->Database->fieldExists('sorting', $strTable))
		{
			$objSorting = $this->Database->prepare("SELECT MAX(sorting) as maxSort FROM " . $strTable)
										 ->execute();
			
			return $objSorting->maxSort + 128;
		}
		
		return 0;
	}		
}

