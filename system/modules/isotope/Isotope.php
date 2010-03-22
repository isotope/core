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
 * @copyright  Winans Creative 2009
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
	 * ID of the default store
	 */
	protected $intDefaultStore;
	
	
	public $Store;
	public $Cart;
	
	
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
		
		if (strlen($_SESSION['isotope']['store_id']))
		{
			$this->overrideStore($_SESSION['isotope']['store_id']);
		}
		else
		{
			$this->resetStore($blnForceDefault);
		}
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
	public function resetStore($blnForceDefault = false)
	{
		global $objPage;
	
		if($blnForceDefault)
		{
			$this->intDefaultStore = $this->getDefaultStore();
		}
		else
		{	
			if($objPage->isotopeStoreConfig)
			{
				//Assign
				$this->intDefaultStore = $objPage->isotopeStoreConfig;
			}
			else
			{
				if($objPage->pid<1)
				{
					$this->intDefaultStore = $this->getDefaultStore();
				}
				else
				{
					//Find (recursive look at parents)
					$this->intDefaultStore = $this->getStoreConfigFromParent($objPage->id);
				}
			}
		}
		
		if ($this->intDefaultStore)
		{
			$this->Store = new IsotopeStore($this->intDefaultStore);
		}
	}
	
	
	/** 
	 * Manual override of the store
	 * 
	 * @param integer $intStoreId;
	 * @return void
	 */
	public function overrideStore($intStoreId)
    {
    	$this->Store = new IsotopeStore($intStoreId);
	}
	
	
	/** 
	 * Get a default store - either one indicated as default in records or else the first record available.
	 *
	 * return integer (store id)
	 */
	protected function getDefaultStore()
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
	 * Recursively look for a store set in a given page. Continue looking at parent pages until one is found or else
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
		}
		else
		{
			return $this->getStoreConfigFromParent($objStoreConfiguration->pid);
		}
	}
	
	
	/**
	 * Calculate price in foreign currencies.
	 */
	public function calculatePrice($fltPrice, $intTaxClass=0)
	{
		// If price or override price is a string
		if (!is_numeric($fltPrice))
			return $fltPrice;
			
		if ($this->Store->priceMultiplier != 1)
		{
			switch ($this->Store->priceCalculateMode)
			{
				case 'mul':
					$fltPrice = $fltPrice * $this->Store->priceCalculateFactor;
					break;
					
				case 'div':
					$fltPrice = $fltPrice / $this->Store->priceCalculateFactor;
					break;
			}
		}
		
		// Possibly add/subtract tax
		if ($intTaxClass > 0)
		{
			$fltPrice = $this->calculateTax($intTaxClass, $fltPrice, false);
		}
		
		if ($this->Store->priceRoundIncrement == '0.05')
		{
			$fltPrice = (round(20*$fltPrice))/20;
		}
		
		$fltPrice = round($fltPrice, $this->Store->priceRoundPrecision);
		
		return $fltPrice;
	}
	
	
	/**
	 * Calculate tax for a certain tax class, based on the current user information 
	 */
	public function calculateTax($intTaxClass, $fltPrice, $blnAdd=true, $arrAddresses=null)
	{
		if (!is_array($arrAddresses))
		{
			$this->import('IsotopeCart', 'Cart');
			$arrAddresses = array('billing'=>$this->Cart->billingAddress, 'shipping'=>$this->Cart->shippingAddress);
		}
		
		$objTaxClass = $this->Database->prepare("SELECT * FROM tl_tax_class WHERE id=?")->limit(1)->execute($intTaxClass);
		
		if (!$objTaxClass->numRows)
			return $fltPrice;
			
		$arrTaxes = array();
		$objIncludes = $this->Database->prepare("SELECT * FROM tl_tax_rate WHERE id=?")->limit(1)->execute($objTaxClass->includes);
		
		if ($objIncludes->numRows)
		{
			$arrTaxRate = deserialize($objIncludes->rate);
			
			// final price / (1 + (tax / 100)
			if (strlen($arrTaxRate['unit']))
			{
				$fltTax = $fltPrice - ($fltPrice / (1 + (floatval($arrTaxRate['value']) / 100)));
			}
			// Full amount
			else
			{
				$fltTax = floatval($arrTaxRate['value']);
			}
			
			if (!$this->useTaxRate($objIncludes, $fltPrice, $arrAddresses))
			{
				$fltPrice -= $fltTax;
			}
			else
			{
				$arrTaxes[$objTaxClass->id.'_'.$objIncludes->id] = array
				(
					'label'			=> (strlen($objTaxClass->label) ? $objTaxClass->label : $objIncludes->label),
					'price'			=> $arrTaxRate['value'] . $arrTaxRate['unit'],
					'total_price'	=> $fltTax,
					'add'			=> false,
				);
			}
		}

		if (!$blnAdd)
		{
			return $fltPrice;
		}
		
		$arrRates = deserialize($objTaxClass->rates);
		if (!is_array($arrRates) || !count($arrRates))
			return $arrTaxes;
		
		$objRates = $this->Database->execute("SELECT * FROM tl_tax_rate WHERE id IN (" . implode(',', $arrRates) . ") ORDER BY id=" . implode(" DESC, id=", $arrRates) . " DESC");
		
		while( $objRates->next() )
		{
			if ($this->useTaxRate($objRates, $fltPrice, $arrAddresses))
			{
				$arrTaxRate = deserialize($objRates->rate);
				
				// final price * (1 + (tax / 100)
				if (strlen($arrTaxRate['unit']))
				{
					$fltTax = ($fltPrice * (1 + (floatval($arrTaxRate['value']) / 100))) - $fltPrice;
				}
				// Full amount
				else
				{
					$fltTax = floatval($arrTaxRate['value']);
				}
				
				$arrTaxes[$objRates->id] = array
				(
					'label'			=> $objRates->label,
					'price'			=> $arrTaxRate['value'] . $arrTaxRate['unit'],
					'total_price'	=> $fltTax,
					'add'			=> true,
				);
				
				if ($objRates->stop)
					break;
			}
		}
		
		return $arrTaxes;
	}
	
	
	public function useTaxRate($objRate, $fltPrice, $arrAddresses)
	{
		$objRate->address = deserialize($objRate->address);
		
		if (is_array($objRate->address) && count($objRate->address))
		{
			foreach( $arrAddresses as $name => $arrAddress )
			{
				if (!in_array($name, $objRate->address))
					continue;
				
				if (strlen($objRate->country) && $objRate->country != $arrAddress['country'])
					return false;
					
				if (strlen($objRate->subdivision) && $objRate->subdivision != $arrAddress['subdivision'])
					return false;
					
				$arrPostal = deserialize($objRate->postal);
				if (is_array($arrPostal) && count($arrPostal) && strlen($arrPostal[0]))
				{
					if (strlen($arrPostal[1]))
					{
						if ($arrPostal[0] > $arrAddress['postal'] || $arrPostal[1] < $arrAddress['postal'])
							return false;
					}
					else
					{
						if ($arrPostal[0] != $arrAddress['postal'])
							return false;
					}
				}
				
				$arrPrice = deserialize($objRate->amount);
				if (is_array($arrPrice) && count($arrPrice) && strlen($arrPrice[0]))
				{
					if (strlen($arrPrice[1]))
					{
						if ($arrPrice[0] > $fltPrice || $arrPrice[1] < $fltPrice)
							return false;
					}
					else
					{
						if ($arrPrice[0] != $fltPrice)
							return false;
					}
				}
			}
		}
			
		return true;
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
		// If price or override price is a string
		if (!is_numeric($fltPrice))
			return $fltPrice;
			
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
	 * @param string $strCurrencyCode (default: null)
	 * @param bool $blnHtml. (default: false)
	 * @return string
	 */
	public function formatPriceWithCurrency($fltPrice, $strCurrencyCode = null, $blnHtml=false)
	{
		// If price or override price is a string
		if (!is_numeric($fltPrice))
			return $fltPrice;
			
		$strCurrency = (strlen($strCurrencyCode) ? $strCurrencyCode : $this->Store->currency);
		
		$strPrice = $this->formatPrice($fltPrice);
		
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
			'firstname'			=> strval($arrData['firstname']),
			'lastname'			=> strval($arrData['lastname']),
			'company'			=> strval($arrData['company']),
			'street_1'			=> strval($arrData['street']),
			'postal'			=> strval($arrData['postal']),
			'city'				=> strval($arrData['city']),
			'subdivision'		=> strval($arrData['state']),
			'country'			=> strval($arrData['country']),
			'phone'				=> strval($arrData['phone']),
			'email'				=> strval($arrData['email']),
			'isDefaultBilling'	=> '1',
			'isDefaultShipping' => '',
		);
	
		
		$this->Database->prepare('INSERT INTO tl_address_book %s')->set($arrSet)->execute();
		$this->Database->prepare("UPDATE tl_member SET disable=0 WHERE id=?")->execute($intId);
	}
	
	
	//!@todo: clean up all getAddress stuff...	
	public function getAddress($strStep = 'billing')
	{	
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
				'street_1'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street_1'],
				'street_2'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street_2'],
				'street_3'		=> $_SESSION['FORM_DATA'][$strStep . '_information_street_3'],
				'city'			=> $_SESSION['FORM_DATA'][$strStep . '_information_city'],
				'subdivision'	=> $_SESSION['FORM_DATA'][$strStep . '_information_subdivision'],
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
	 * Generate an address string
	 * 
	 * @access protected
	 * @param array $arrAddress
	 * @return string
	 */
	public function generateAddressString($arrAddress, $arrFields=null)
	{
		if (!is_array($arrAddress) || !count($arrAddress))
			return $arrAddress;
			
		if (!is_array($arrFields))
		{
			$arrFields = $this->Store->billing_fields;
		}
		
		// We need a country to format the address, user default country if none is available
		if (!strlen($arrAddress['country']))
		{
			$arrAddress['country'] = $this->Store->country;
		}
		
		$arrCountries = $this->getCountries();
		
		$strFormat = $GLOBALS['ISO_ADR'][$arrAddress['country']];
		$arrAddress['country'] = $arrCountries[$arrAddress['country']];
	
		$arrSearch = $arrReplace = array();
		foreach( $arrFields as $strField )
		{
			if ($strField == 'subdivision' && strlen($arrAddress['subdivision']))
			{
				if (!is_array($GLOBALS['TL_LANG']['DIV']))
				{
					$this->loadLanguageFile('subdivisions');
				}
				
				list($country, $subdivion) = explode('-', $arrAddress['subdivision']);
				$arrAddress['subdivision'] = $GLOBALS['TL_LANG']['DIV'][$country][$arrAddress['subdivision']];
			
				$arrSearch[] = '{subdivision-abbr}';
				$arrReplace[] = $subdivion;
			}
			
			$arrSearch[] = '{'.$strField.'}';
			$arrReplace[] = $arrAddress[$strField];
		}

		// Parse format
		$strAddress = str_replace($arrSearch, $arrReplace, $strFormat);
	
		// Remove empty tags
		$strAddress = preg_replace('(\{[^}]+\})', '', $strAddress);
	
		// Remove double line breaks
		do
		{
			$strAddress = str_replace('<br /><br />', '<br />', trim($strAddress), $found);
		}
		while ($found > 0);
		
		// Remove line break at beginning of address
		if (strpos($strAddress, '<br />') === 0)
			$strAddress = substr($strAddress, 6);
			
		// Remove line break at end of address
		if (substr($strAddress, -6) == '<br />')
			$strAddress = substr($strAddress, 0, -6);
	
		return $strAddress;
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
		$objEmail->from = ($objMail->originateFromCustomerEmail && strlen($arrData['customer_email'])) ? $arrData['customer_email'] : $objMail->sender;
		$objEmail->fromName = ($objMail->originateFromCustomerEmail && strlen($arrData['customer_name'])) ? $arrData['customer_name'] : $objMail->senderName;
		$objEmail->subject = $this->parseSimpleTokens($objMail->subject, $arrData);
		
		if (strlen($arrData['customer_email']))
		{
			$objEmail->replyTo((strlen($arrData['customer_name']) ? sprintf('%s <%s>', $arrData['customer_name'], $arrData['customer_email']) : $arrData['customer_email']));
		}
		
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
				$objTemplate->body = $objMail->html;
				$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
				$objTemplate->css = $css;
	
				// Parse template
				$objEmail->html = $this->parseSimpleTokens($this->replaceInsertTags($objParsedTemplate), $arrData);
				$objEmail->imageDir = TL_ROOT . '/';
			}
		}
		
		if (strlen($objMail->cc))
		{
			$arrRecipients = trimsplit(',', $objMail->cc);
			foreach( $arrRecipients as $recipient )
			{
				$objEmail->sendCc($recipient);
			}
		}
		
		if (strlen($objMail->bcc))
		{
			$arrRecipients = trimsplit(',', $objMail->ccc);
			foreach( $arrRecipients as $recipient )
			{
				$objEmail->sendBcc($recipient);
			}
		}
		
		$attachments = deserialize($objMail->attachments);
	   	if(is_array($attachments) && count($attachments) > 0)
		{
			foreach($attachments as $attachment)
			{
				if(file_exists(TL_ROOT . '/' . $attachment))
				{
					$objEmail->attachFile(TL_ROOT . '/' . $attachment);
				}
			}
		}
		
		$objEmail->sendTo($strRecipient);
	}

	
	public function generatePage(Database_Result $objPage, Database_Result $objLayout, PageRegular $objPage)
	{
		$objTemplate = new FrontendTemplate('ajax_load_overlay');
		
		$strImagePath = "system/themes/default/images/loading.gif";	//TODO: set in module.
		
		$objTemplate->ajaxLoadingMessage = $GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage'];
		$objTemplate->ajaxLoadingImage = $this->generateImage($strImagePath);
		
		$objPage->Template->header .= $objTemplate->parse();	
	
	}
	
	
	/**
	 * Merge the OptionDataWizard and OptionWizard data.
	 * This is a callback for attributes (eg. select menu).
	 */
	public function mergeOptionData($arrData, $arrAttributes, &$objWidget=null, &$objProduct=null)
	{
		if (TL_MODE != 'FE' || !$objProduct || !$objWidget)
			return $arrData;
			
		$arrOptionData = $objProduct->{$objWidget->name};
		
		if (is_array($arrOptionData))
		{
			$arrOptions = array();
			
			foreach( deserialize($arrAttributes['option_list'], true) as $option )
			{
				if (!is_array($arrOptionData[$option['value']]) || !$arrOptionData[$option['value']]['disable'])
				{
					$arrOptions[] = array
					(
						'label'		=> ((strlen($arrOptionData[$option['value']]['label']) && !$arrOptionData[$option['value']]['inherit']) ? $arrOptionData[$option['value']]['label'] : $option['label']),
						'value'		=> $option['value'],
						'default'	=> $option['default'],
						'group'		=> $option['group'],
					);
				}
			}
			
			$objWidget->options = $arrOptions;
		}
		
		return $arrData;
	}
	
	
	/**
	 * Callback for isoButton Hook.
	 */
	public function addToCartButton($arrButtons)
	{
		$arrButtons['add_to_cart'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('IsotopeCart', 'addProduct'));
		
		return $arrButtons;
	}
}

