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
 

class Isotope extends Controller
{

	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;
	
	
	public $Config;
	public $Cart;
	public $Order;
	
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

		if (strlen($_SESSION['ISOTOPE']['config_id']))
		{
			$this->overrideConfig($_SESSION['ISOTOPE']['config_id']);
		}
		else
		{
			$this->resetConfig();
		}
		
		if (TL_MODE == 'FE')
		{
			$this->Cart = new IsotopeCart();
			$this->Cart->initializeCart($this->Config->id, $this->Config->cookie_timeout);
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
	 * Set the default store config
	 */
	public function resetConfig()
	{
		$intConfig = null;
		
		if(TL_MODE == 'FE')
		{
			global $objPage;
			
			$intConfig = $this->Database->prepare("SELECT iso_config FROM tl_page WHERE id=?")->execute($objPage->rootId)->iso_config;
		}
		
		if (!$intConfig)
		{
			$intConfig = $this->Database->execute("SELECT id FROM tl_iso_config WHERE fallback='1'")->id;
		}

		if(!$intConfig)
		{
			if (TL_MODE == 'BE')
			{
				$_SESSION['TL_ERROR'] = array($GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration']);
				
				if ($this->Input->get('do') == 'iso_products')
					$this->redirect('typolight/main.php?do=iso_setup&table=tl_iso_config&act=create');
			}
			else
			{
				throw new Exception($GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet']);
			}
			
			return;
		}
		
		$this->Config = new IsotopeConfig($intConfig);
	}
	
	
	/** 
	 * Manual override of the store configuration
	 */
	public function overrideConfig($intConfig)
    {
    	try
		{
			$objConfig = new IsotopeConfig($intConfig);
			$this->Config = $objConfig;
		}
		catch (Exception $e)
		{
			$this->resetConfig();
		}
	}

	
	/**
	 * Calculate price trough hook and foreign prices.
	 */
	public function calculatePrice($fltPrice, &$objSource, $strField, $intTaxClass=0)
	{
		// If price or override price is a string
		if (!is_numeric($fltPrice))
			return $fltPrice;
			
		// HOOK for altering prices
		if (isset($GLOBALS['TL_HOOKS']['iso_calculatePrice']) && is_array($GLOBALS['TL_HOOKS']['iso_calculatePrice']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_calculatePrice'] as $callback)
			{
				$this->import($callback[0]);
				$fltPrice = $this->$callback[0]->$callback[1]($fltPrice, $objSource, $strField, $intTaxClass);
			}
		}
			
		if ($this->Config->priceMultiplier != 1)
		{
			switch ($this->Config->priceCalculateMode)
			{
				case 'mul':
					$fltPrice = $fltPrice * $this->Config->priceCalculateFactor;
					break;
					
				case 'div':
					$fltPrice = $fltPrice / $this->Config->priceCalculateFactor;
					break;
			}
		}
		
		// Possibly add/subtract tax
		if ($intTaxClass > 0)
		{
			$fltPrice = $this->calculateTax($intTaxClass, $fltPrice, false);
		}
		
		if ($this->Config->priceRoundIncrement == '0.05')
		{
			$fltPrice = (round(20*$fltPrice))/20;
		}
		
		$fltPrice = round($fltPrice, $this->Config->priceRoundPrecision);
		
		return $fltPrice;
	}
	
	
	/**
	 * Calculate tax for a certain tax class, based on the current user information 
	 */
	public function calculateTax($intTaxClass, $fltPrice, $blnAdd=true, $arrAddresses=null)
	{
		if (!is_array($arrAddresses))
		{
			$arrAddresses = array('billing'=>$this->Cart->billingAddress, 'shipping'=>$this->Cart->shippingAddress);
		}
		
		$objTaxClass = $this->Database->prepare("SELECT * FROM tl_iso_tax_class WHERE id=?")->limit(1)->execute($intTaxClass);
		
		if (!$objTaxClass->numRows)
			return $fltPrice;
			
		$arrTaxes = array();
		$objIncludes = $this->Database->prepare("SELECT * FROM tl_iso_tax_rate WHERE id=?")->limit(1)->execute($objTaxClass->includes);
		
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
		
		$objRates = $this->Database->execute("SELECT * FROM tl_iso_tax_rate WHERE id IN (" . implode(',', $arrRates) . ") ORDER BY id=" . implode(" DESC, id=", $arrRates) . " DESC");
		
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
		if ($objRate->config > 0 && $objRate->config != $this->Config->id)
			return false;
			
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
	 * Format given price according to store config settings.
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
			
		$arrFormat = $GLOBALS['ISO_NUM'][$this->Config->currencyFormat];
		
		if (!is_array($arrFormat) || !count($arrFormat) == 3)
			return $fltPrice;
		
		return number_format($fltPrice, $arrFormat[0], $arrFormat[1], $arrFormat[2]);
	}
	
	
	/**
	 * Format given price according to store config settings, including currency representation.
	 * 
	 * @access public
	 * @param float $fltPrice
	 * @param string $strCurrencyCode (default: null)
	 * @param bool $blnHtml. (default: false)
	 * @return string
	 */
	public function formatPriceWithCurrency($fltPrice, $blnHtml=true, $strCurrencyCode = null)
	{
		// If price or override price is a string
		if (!is_numeric($fltPrice))
			return $fltPrice;
			
		$strCurrency = (strlen($strCurrencyCode) ? $strCurrencyCode : $this->Config->currency);
		
		$strPrice = $this->formatPrice($fltPrice);
		
		if ($this->Config->currencySymbol && strlen($GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency]))
		{
			$strCurrency = ($blnHtml ? '<span class="currency">' : '') . $GLOBALS['TL_LANG']['CUR_SYMBOL'][$strCurrency] . ($blnHtml ? '</span>' : '');
		}
		else
		{
			$strCurrency = ($this->Config->currencyPosition == 'right' ? ' ' : '') . ($blnHtml ? '<span class="currency">' : '') . $strCurrency . ($blnHtml ? '</span>' : '') . ($this->Config->currencyPosition == 'left' ? ' ' : '');
		}
		
		if ($this->Config->currencyPosition == 'right')
		{
			return $strPrice . $strCurrency;
		}
		
		return $strCurrency . $strPrice;
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
			$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")
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
			$arrFields = deserialize($this->Config->billing_fields, true);
		}
		
		// We need a country to format the address, user default country if none is available
		if (!strlen($arrAddress['country']))
		{
			$arrAddress['country'] = $this->Config->country;
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
		
		// Add HTML content
		if (!$objMail->textOnly && strlen($objMail->html))
		{
			// Get mail template
			$objTemplate = new FrontendTemplate((strlen($objMail->template) ? $objMail->template : 'mail_default'));

			$objTemplate->title = $objMail->subject;
			$objTemplate->body = $objMail->html;
			$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
			$objTemplate->css = $css;

			// Parse template
			$objEmail->html = $this->parseSimpleTokens($this->replaceInsertTags($objTemplate->parse()), $arrData);
			$objEmail->imageDir = TL_ROOT . '/';
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
	
	
	/**
	 * Merge the OptionDataWizard and OptionWizard data.
	 * This is a callback for attributes (eg. select menu).
	 */
	public function mergeOptionData($strField, $arrData, &$objProduct=null)
	{
		if (TL_MODE != 'FE' || !is_object($objProduct))
			return $arrData;
			
		$arrProductData = $objProduct->getData();
		$arrOptionData = $arrProductData[$strField];
		
		if (is_array($arrOptionData))
		{
			foreach( $arrData['options'] as $k => $v )
			{
				if (is_array($v))
				{
					foreach( $v as $kk => $vv )
					{
						if ($arrOptionData[$kk]['disable'])
						{
							unset($arrData['options'][$k][$kk]);
							continue(2);
						}
					}
					
					if (strlen($arrOptionData[$kk]['label']) && !$arrOptionData[$kk]['inherit'])
					{
						$arrData['options'][$k][$kk] = $arrOptionData[$kk]['label'];
					}
				}
				else
				{
					if ($arrOptionData[$k]['disable'])
					{
						unset($arrData['options'][$k]);
						continue;
					}
					
					if (strlen($arrOptionData[$k]['label']) && !$arrOptionData[$k]['inherit'])
					{
						$arrData['options'][$k] = $arrOptionData[$k]['label'];
					}
				}
			}
		}
		
		return $arrData;
	}
	
	public function mergeConditionalOptionData($strField, $arrData, &$objProduct=null)
	{
		$arrData['attributes']['add_to_product_variants'] = false;
		$arrData['eval']['conditionField'] = $arrData['attributes']['conditionField'] . (is_object($objProduct) ? '_'.$objProduct->id : '');

		return $this->mergeOptionData($strField, $arrData, $objProduct);
	}
	
	
	/**
	 * Callback for isoButton Hook.
	 */
	public function defaultButtons($arrButtons)
	{
		$arrButtons['update'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update']);
		$arrButtons['add_to_cart'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('IsotopeFrontend', 'addToCart'));
		
		return $arrButtons;
	}
	
	
	/**
	 * Replaces Isotope-specific InsertTags in Frontend.
	 * 
	 * @access public
	 * @param string $strTag
	 * @return mixed
	 */
	public function replaceIsotopeTags($strTag)
	{
		$arrTag = trimsplit('::', $strTag);
		
		if (count($arrTag) == 2 && $arrTag[0] == 'isotope')
		{
			switch( $arrTag[1] )
			{
				case 'cart_items';
					return $this->Cart->items;
					break;
					
				case 'cart_products';
					return $this->Cart->products;
					break;
					
				case 'cart_items_label';
					$intCount = $this->Cart->items;
					if (!$intCount)
						return '';
					
					return $intCount == 1 ? ('('.$GLOBALS['TL_LANG']['ISO']['productSingle'].')') : sprintf(('('.$GLOBALS['TL_LANG']['ISO']['productMultiple'].')'), $intCount);
					break;
					
				case 'cart_products_label';
					$intCount = $this->Cart->products;
					if (!$intCount)
						return '';
					
					return $intCount == 1 ? ('('.$GLOBALS['TL_LANG']['ISO']['productSingle'].')') : sprintf(('('.$GLOBALS['TL_LANG']['ISO']['productMultiple'].')'), $intCount);
					break;
			}
			
			return '';
		}
		
		return false;
	}
	
	
	/**
	 * Intermediate-Function to allow DCA class to be loaded.
	 */
	public function loadDataContainer($strTable)
	{
		if ($strTable == 'tl_iso_products')
		{
			$this->import('tl_iso_products');
			$this->tl_iso_products->loadProductsDCA();
		}
	}
	
	
	/**
	 * Standardize and calculate the total of multiple weights.
	 *
	 * It's probably faster in theory to convert only the total to the final unit, and not each product weight.
	 * However, we might loose precision, not sure about that.
	 * Based on formulas found at http://jumk.de/calc/gewicht.shtml
	 */
	public function calculateWeight($arrWeights, $strUnit)
	{
		if (!is_array($arrWeights) || !count($arrWeights))
			return 0;
			
		$fltWeight = 0;

		foreach( $arrWeights as $weight )
		{
			if (is_array($weight) && $weight['value'] > 0 && strlen($weight['unit']))
			{
				$fltWeight += $this->convertWeight(floatval($weight['value']), $weight['unit'], 'kg');
			}
		}
		
		return $this->convertWeight($fltWeight, 'kg', $strUnit);
	}
	
	
	/**
	 * Convert weight units.
	 * Supported source/target units: mg, g, kg, t, ct, oz, lb, st, grain
	 */
	public function convertWeight($fltWeight, $strSourceUnit, $strTargetUnit)
	{
		switch( $strSourceUnit )
		{
			case 'mg':
				return $this->convertWeight(($fltWeight / 1000000), 'kg', $strTargetUnit);
				
			case 'g':
				return $this->convertWeight(($fltWeight / 1000), 'kg', $strTargetUnit);
				
			case 'kg':
				switch( $strTargetUnit )
				{
					case 'mg':
						return $fltWeight * 1000000;
						
					case 'g':
						return $fltWeight * 1000;
						
					case 'kg':
						return $fltWeight;
						
					case 't':
						return $fltWeight / 1000;
						
					case 'ct':
						return $fltWeight * 5000;
						
					case 'oz':
						return $fltWeight / 28.35 * 1000;
						
					case 'lb':
						return $fltWeight / 0.45359243;
						
					case 'st':
						return $fltWeight / 6.35029318;
					
					case 'grain':
						return $fltWeight / 64.79891 * 1000000;
						
					default:
						throw new Exception('Unknown target weight unit "' . $strTargetUnit . '"');
				}
				
			case 't':
				return $this->convertWeight(($fltWeight * 1000), 'kg', $strTargetUnit);
				
			case 'ct':
				return $this->convertWeight(($fltWeight / 5000), 'kg', $strTargetUnit);
				
			case 'oz':
				return $this->convertWeight(($fltWeight * 28.35 / 1000), 'kg', $strTargetUnit);
				
			case 'lb':
				return $this->convertWeight(($fltWeight * 0.45359243), 'kg', $strTargetUnit);
				
			case 'st':
				return $this->convertWeight(($fltWeight * 6.35029318), 'kg', $strTargetUnit);

			case 'grain':
				return $this->convertWeight(($fltWeight * 64.79891 / 1000000), 'kg', $strTargetUnit);
				
			default:
				throw new Exception('Unknown source weight unit "' . $strSourceUnit . '"');
		}
	}
	
	
	public function validateRegexp($strRegexp, $varValue, Widget $objWidget)
	{
		switch( $strRegexp )
		{
			case 'price':
				if (!preg_match('/^[\d \.-]*$/', $varValue))
				{
					$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['digit'], $objWidget->label));
				}
				return true;
				break;
				
			case 'calc':
				if(!preg_match('/^[-+]\d+(\.\d{1,2})?%?$/', $varValue))
				{															
					$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['calc'], $objWidget->label));
				}
				return true;
				break;
		}
		
		return false;
	}
}

