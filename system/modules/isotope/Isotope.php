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
 * Class Isotope
 * 
 * The base class for all Isotope components.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Isotope extends Controller
{

	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;

	/**
	 * Cache select statement to load product data
	 * @var string
	 */
	protected $strSelect;

	/**
	 * Current config instance
	 * @var object
	 */
	public $Config;

	/**
	 * Current cart instance
	 * @var object
	 */
	public $Cart;

	/**
	 * Current order instance
	 * @var object
	 */
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

		// Make sure field data is available
		$this->loadDataContainer('tl_iso_products');
		$this->loadLanguageFile('tl_iso_products');

		if (strlen($_SESSION['ISOTOPE']['config_id']))
		{
			$this->overrideConfig($_SESSION['ISOTOPE']['config_id']);
		}
		else
		{
			$this->resetConfig();
		}

		if (TL_MODE == 'FE' && strpos($this->Environment->script, 'postsale.php') === false)
		{
			$this->Cart = new IsotopeCart();
			$this->Cart->initializeCart((int)$this->Config->id, (int)$this->Config->store_id);

			// Initialize request cache for product list filters
			if ($this->Input->get('isorc') != '')
			{
				$objRequestCache = $this->Database->prepare("SELECT * FROM tl_iso_requestcache WHERE id=? AND store_id=?")->execute($this->Input->get('isorc'), $this->Config->store_id);

				if ($objRequestCache->numRows)
				{
					$GLOBALS['ISO_FILTERS'] = deserialize($objRequestCache->filters);
					$GLOBALS['ISO_SORTING'] = deserialize($objRequestCache->sorting);
					$GLOBALS['ISO_LIMIT'] = deserialize($objRequestCache->limits);
				}
			}
		}
	}


	/**
	 * Instantiate the Isotope object
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

		if ($this->Database->tableExists('tl_iso_config'))
		{
			if (TL_MODE == 'FE')
			{
				global $objPage;
				$objConfig = $this->Database->prepare("SELECT c.* FROM tl_iso_config c LEFT OUTER JOIN tl_page p ON p.iso_config=c.id WHERE (p.id=" . (int)$objPage->rootId . " OR c.fallback='1') ORDER BY c.fallback")->limit(1)->execute();
			}
			else
			{
				$objConfig = $this->Database->execute("SELECT * FROM tl_iso_config WHERE fallback='1'");
			}
		}

		if ($objConfig === null || !$objConfig->numRows)
		{
			// Display error message in Isotope related backend modules
			if (TL_MODE == 'BE')
			{
				$do = $this->Input->get('do');

				if ($GLOBALS['BE_MOD']['isotope'][$do] != '')
				{
					$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration'];

					if ($do == 'iso_products')
					{
						$this->redirect($this->Environment->script.'?do=iso_setup&mod=configs&table=tl_iso_config&act=create');
					}
				}
			}
			else
			{
				trigger_error($GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet'], E_USER_WARNING);
			}

			return;
		}

		$this->Config = new IsotopeConfig();
		$this->Config->setFromRow($objConfig, 'tl_iso_config', 'id');
	}


	/**
	 * Manual override of the store configuration
	 * @param integer
	 */
	public function overrideConfig($intConfig)
    {
		$this->Config = new IsotopeConfig();

		if (!$this->Config->findBy('id', $intConfig))
		{
			$this->resetConfig();
		}
	}


	/**
	 * Calculate price trough hook and foreign prices
	 * @param float
	 * @param object
	 * @param string
	 * @param integer
	 * @return float
	 */
	public function calculatePrice($fltPrice, &$objSource, $strField, $intTaxClass=0)
	{
		if (!is_numeric($fltPrice))
		{
			return $fltPrice;
		}

		// HOOK for altering prices
		if (isset($GLOBALS['ISO_HOOKS']['calculatePrice']) && is_array($GLOBALS['ISO_HOOKS']['calculatePrice']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['calculatePrice'] as $callback)
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

		return $this->roundPrice($fltPrice);
	}


	/**
	 * Calculate tax for a certain tax class, based on the current user information
	 * @param integer
	 * @param float
	 * @param boolean
	 * @param array
	 * @return array
	 */
	public function calculateTax($intTaxClass, $fltPrice, $blnAdd=true, $arrAddresses=null)
	{
		if ($intTaxClass == 0)
		{
			return $fltPrice;
		}

		if (!is_array($arrAddresses))
		{
			$arrAddresses = array('billing'=>$this->Cart->billingAddress, 'shipping'=>$this->Cart->shippingAddress);
		}

		$objTaxClass = $this->Database->prepare("SELECT * FROM tl_iso_tax_class WHERE id=?")->limit(1)->execute($intTaxClass);

		if (!$objTaxClass->numRows)
		{
			return $fltPrice;
		}

		// HOOK for altering taxes
		if (isset($GLOBALS['ISO_HOOKS']['calculateTax']) && is_array($GLOBALS['ISO_HOOKS']['calculateTax']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['calculateTax'] as $callback)
			{
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($objTaxClass, $fltPrice, $blnAdd, $arrAddresses);

				if ($varValue !== false)
				{
					return $varValue;
				}
			}
		}

		$arrTaxes = array();
		$objIncludes = $this->Database->prepare("SELECT * FROM tl_iso_tax_rate WHERE id=?")->limit(1)->execute($objTaxClass->includes);

		if ($objIncludes->numRows)
		{
			$arrTaxRate = deserialize($objIncludes->rate);

			// Final price / (1 + (tax / 100)
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
				$arrTaxes[$objTaxClass->id . '_' . $objIncludes->id] = array
				(
					'label'			=> $this->translate($objTaxClass->label ? $objTaxClass->label : ($objIncludes->label ? $objIncludes->label : $objIncludes->name)),
					'price'			=> $arrTaxRate['value'] . $arrTaxRate['unit'],
					'total_price'	=> $this->roundPrice($fltTax, $objTaxClass->applyRoundingIncrement),
					'add'			=> false,
				);
			}
		}

		if (!$blnAdd)
		{
			return $fltPrice;
		}

		$arrRates = deserialize($objTaxClass->rates);

		// Return if there are no rates
		if (!is_array($arrRates) || !count($arrRates))
		{
			return $arrTaxes;
		}

		$objRates = $this->Database->execute("SELECT * FROM tl_iso_tax_rate WHERE id IN (" . implode(',', $arrRates) . ") ORDER BY id=" . implode(" DESC, id=", $arrRates) . " DESC");

		while ($objRates->next())
		{
			if ($this->useTaxRate($objRates, $fltPrice, $arrAddresses))
			{
				$arrTaxRate = deserialize($objRates->rate);

				// Final price * (1 + (tax / 100)
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
					'label'			=> $this->translate($objRates->label ? $objRates->label : $objRates->name),
					'price'			=> $arrTaxRate['value'] . $arrTaxRate['unit'],
					'total_price'	=> $this->roundPrice($fltTax, $objTaxClass->applyRoundingIncrement),
					'add'			=> true,
				);

				if ($objRates->stop)
				{
					break;
				}
			}
		}

		return $arrTaxes;
	}


	/**
	 * Determine whether to use the tax rate or not
	 * @param object
	 * @param float
	 * @param array
	 * @return boolean
	 */
	public function useTaxRate($objRate, $fltPrice, $arrAddresses)
	{
		if ($objRate->config > 0 && $objRate->config != $this->Config->id)
		{
			return false;
		}

		$objRate->address = deserialize($objRate->address);

		// HOOK for altering taxes
		if (isset($GLOBALS['ISO_HOOKS']['useTaxRate']) && is_array($GLOBALS['ISO_HOOKS']['useTaxRate']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['useTaxRate'] as $callback)
			{
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($objRate, $fltPrice, $arrAddresses);

				if ($varValue !== true)
				{
					return false;
				}
			}
		}

		if (is_array($objRate->address) && count($objRate->address))
		{
			foreach ($arrAddresses as $name => $arrAddress)
			{
				if (!in_array($name, $objRate->address))
				{
					continue;
				}

				if (strlen($objRate->country) && $objRate->country != $arrAddress['country'])
				{
					return false;
				}

				if (strlen($objRate->subdivision) && $objRate->subdivision != $arrAddress['subdivision'])
				{
					return false;
				}
				
				// Check if address has a valid postal code
				if ($objRate->postalCodes != '')
				{
					$arrCodes = IsotopeFrontend::parsePostalCodes($objRate->postalCodes);
					
					if (!in_array($arrAddress['postal'], $arrCodes))
					{
						return false;
					}
				}

				$arrPostal = deserialize($objRate->postal);

				if (is_array($arrPostal) && count($arrPostal) && strlen($arrPostal[0]))
				{
					if (strlen($arrPostal[1]))
					{
						if ($arrPostal[0] > $arrAddress['postal'] || $arrPostal[1] < $arrAddress['postal'])
						{
							return false;
						}
					}
					else
					{
						if ($arrPostal[0] != $arrAddress['postal'])
						{
							return false;
						}
					}
				}

				$arrPrice = deserialize($objRate->amount);

				if (is_array($arrPrice) && count($arrPrice) && strlen($arrPrice[0]))
				{
					if (strlen($arrPrice[1]))
					{
						if ($arrPrice[0] > $fltPrice || $arrPrice[1] < $fltPrice)
						{
							return false;
						}
					}
					else
					{
						if ($arrPrice[0] != $fltPrice)
						{
							return false;
						}
					}
				}
			}
		}

		return true;
	}
	
	
	/**
	 * Rounds a price according to store config settings
	 * @param float original value
	 * @param bool apply rounding increment
	 * @return float rounded value 
	 */
	public function roundPrice($fltValue, $blnApplyRoundingIncrement=true)
	{
		if ($blnApplyRoundingIncrement && $this->Config->priceRoundIncrement == '0.05')
		{
			$fltValue = (round(20 * $fltValue)) / 20;
		}

		return round($fltValue, $this->Config->priceRoundPrecision);
	}


	/**
	 * Format given price according to store config settings
	 * @param float
	 * @return float
	 */
	public function formatPrice($fltPrice)
	{
		// If price or override price is a string
		if (!is_numeric($fltPrice))
		{
			return $fltPrice;
		}

		$arrFormat = $GLOBALS['ISO_NUM'][$this->Config->currencyFormat];

		if (!is_array($arrFormat) || !count($arrFormat) == 3)
		{
			return $fltPrice;
		}

		return number_format($fltPrice, $arrFormat[0], $arrFormat[1], $arrFormat[2]);
	}


	/**
	 * Format given price according to store config settings, including currency representation
	 * @param float
	 * @param boolean
	 * @param string
	 * @return string
	 */
	public function formatPriceWithCurrency($fltPrice, $blnHtml=true, $strCurrencyCode=null)
	{
		// If price or override price is a string
		if (!is_numeric($fltPrice))
		{
			return $fltPrice;
		}

		$strCurrency = ($strCurrencyCode != '' ? $strCurrencyCode : $this->Config->currency);
		$strPrice = $this->formatPrice($fltPrice);

		if ($this->Config->currencySymbol && $GLOBALS['ISO_LANG']['CUR_SYMBOL'][$strCurrency] != '')
		{
			$strCurrency = (($this->Config->currencyPosition == 'right' && $this->Config->currencySpace) ? ' ' : '') . ($blnHtml ? '<span class="currency">' : '') . $GLOBALS['ISO_LANG']['CUR_SYMBOL'][$strCurrency] . ($blnHtml ? '</span>' : '') . (($this->Config->currencyPosition == 'left' && $this->Config->currencySpace) ? ' ' : '');
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


	/**
	 * Get the address details and return it as array
	 * @todo clean up all getAddress stuff...
	 * @param string
	 * @return array
	 */
	public function getAddress($strStep = 'billing')
	{
		if ($strStep == 'shipping' && !FE_USER_LOGGED_IN && $_SESSION['FORM_DATA']['shipping_address'] == -1)
		{
			$strStep = 'billing';
		}

		if ($_SESSION['FORM_DATA'][$strStep.'_address'] && !isset($_SESSION['FORM_DATA']['billing_address']))
		{
			return false;
		}

		$intAddressId = $_SESSION['FORM_DATA'][$strStep . '_address'];

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
			$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")->limit(1)->execute($intAddressId);

			// Return if no address was found
			if ($objAddress->numRows < 1)
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
	 * @param array
	 * @param array
	 * @return string
	 */
	public function generateAddressString($arrAddress, $arrFields=null)
	{
		if (!is_array($arrAddress) || !count($arrAddress))
		{
			return $arrAddress;
		}

		if (!is_array($GLOBALS['ISO_ADR']))
		{
			$this->loadLanguageFile('countries');
		}

		if (!is_array($arrFields))
		{
			$arrFields = deserialize($this->Config->billing_fields, true);
		}

		// We need a country to format the address, user default country if none is available
		if (!strlen($arrAddress['country']))
		{
			$arrAddress['country'] = $this->Config->country;
		}

		$arrSearch = array();
		$arrReplace = array();

		foreach ($arrFields as $arrField)
		{
			$strField = $arrField['value'];

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

			$arrSearch[] = '{' . $strField . '}';
			$arrReplace[] = $this->formatValue('tl_iso_addresses', $strField, $arrAddress[$strField]);
		}

		// Parse format
		$strAddress = str_replace($arrSearch, $arrReplace, $GLOBALS['ISO_ADR'][$arrAddress['country']]);

		// Remove empty tags
		$strAddress = preg_replace('(\{[^}]+\})', '', $strAddress);

		// Remove empty brackets
		$strAddress = str_replace('()', '', $strAddress);

		// Remove double line breaks
		do
		{
			$strAddress = str_replace('<br /><br />', '<br />', trim($strAddress), $found);
		}
		while ($found > 0);

		// Remove line break at beginning of address
		if (strpos($strAddress, '<br />') === 0)
		{
			$strAddress = substr($strAddress, 6);
		}

		// Remove line break at end of address
		if (substr($strAddress, -6) == '<br />')
		{
			$strAddress = substr($strAddress, 0, -6);
		}

		return $strAddress;
	}


	/**
	 * Send an email using the isotope e-mail templates
	 * @param integer
	 * @param string
	 * @param string
	 * @param array
	 * @param string
	 * @param object
	 */
	public function sendMail($intId, $strRecipient, $strLanguage, $arrData, $strReplyTo='', $objCollection=null)
	{
		try
		{
			$objEmail = new IsotopeEmail($intId, $strLanguage, $objCollection);

			if ($strReplyTo != '')
			{
				$objEmail->replyTo($strReplyTo);
			}

			$objEmail->send($strRecipient, $arrData);
		}
		catch (Exception $e)
		{
			$this->log('Isotope email error: ' . $e->getMessage(), __METHOD__, TL_ERROR);
		}
	}


	/**
	 * Update ConditionalSelect to include the product ID in conditionField
	 * @param string
	 * @param array
	 * @param object
	 * @return array
	 */
	public function mergeConditionalOptionData($strField, $arrData, &$objProduct=null)
	{
		$arrData['eval']['conditionField'] = $arrData['attributes']['conditionField'] . (is_object($objProduct) ? '_' . $objProduct->formSubmit : '');
		return $arrData;
	}


	/**
	 * Callback for isoButton Hook
	 * @param array
	 * @return array
	 */
	public function defaultButtons($arrButtons)
	{
		$arrButtons['update'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update']);
		$arrButtons['add_to_cart'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('IsotopeFrontend', 'addToCart'));

		return $arrButtons;
	}


	/**
	 * Intermediate-Function to allow DCA class to be loaded
	 * @param string
	 */
	public function loadProductsDataContainer($strTable)
	{
		if ($strTable == 'tl_iso_products')
		{
			$this->import('tl_iso_products');
			$this->tl_iso_products->loadProductsDCA();
		}
		elseif ($strTable == 'tl_member' && $this->Config->limitMemberCountries)
		{
			$arrCountries = array_unique(array_merge((array)deserialize($this->Config->billing_countries), (array)deserialize($this->Config->shipping_countries)));
			$GLOBALS['TL_DCA']['tl_member']['fields']['country']['options'] = array_intersect_key($GLOBALS['TL_DCA']['tl_member']['fields']['country']['options'], array_flip($arrCountries));
		}
	}


	/**
	 * Standardize and calculate the total of multiple weights
	 *
	 * It's probably faster in theory to convert only the total to the final unit, and not each product weight.
	 * However, we might loose precision, not sure about that.
	 * Based on formulas found at http://jumk.de/calc/gewicht.shtml
	 * @param array
	 * @param string
	 * @return mixed
	 */
	public function calculateWeight($arrWeights, $strUnit)
	{
		if (!is_array($arrWeights) || !count($arrWeights))
		{
			return 0;
		}

		$fltWeight = 0;

		foreach ($arrWeights as $weight)
		{
			if (is_array($weight) && $weight['value'] > 0 && strlen($weight['unit']))
			{
				$fltWeight += $this->convertWeight(floatval($weight['value']), $weight['unit'], 'kg');
			}
		}

		return $this->convertWeight($fltWeight, 'kg', $strUnit);
	}


	/**
	 * Convert weight units
	 * Supported source/target units: mg, g, kg, t, ct, oz, lb, st, grain
	 * @param float
	 * @param string
	 * @param string
	 * @return mixed
	 * @throws Exception
	 */
	public function convertWeight($fltWeight, $strSourceUnit, $strTargetUnit)
	{
		switch ($strSourceUnit)
		{
			case 'mg':
				return $this->convertWeight(($fltWeight / 1000000), 'kg', $strTargetUnit);

			case 'g':
				return $this->convertWeight(($fltWeight / 1000), 'kg', $strTargetUnit);

			case 'kg':
				switch ($strTargetUnit)
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


	/**
	 * Validate a custom regular expression
	 * @param string
	 * @param mixed
	 * @param object
	 * @return boolean
	 */
	public function validateRegexp($strRegexp, $varValue, Widget $objWidget)
	{
		switch ($strRegexp)
		{
			case 'price':
				if (!preg_match('/^[\d \.-]*$/', $varValue))
				{
					$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['digit'], $objWidget->label));
				}
				return true;
				break;

			case 'discount':
				if (!preg_match('/^[-+]\d+(\.\d{1,2})?%?$/', $varValue))
				{
					$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['discount'], $objWidget->label));
				}
				return true;
				break;

			case 'surcharge':
				if (!preg_match('/^-?\d+(\.\d{1,2})?%?$/', $varValue))
				{
					$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['surcharge'], $objWidget->label));
				}
				return true;
				break;
		}

		return false;
	}


	/**
	 * Translate a value using the tl_iso_label table
	 * @param mixed
	 * @param boolean
	 * @return mixed
	 */
	public function translate($label, $language=false)
	{
		if (!in_array('isotope_multilingual', $this->Config->getActiveModules()))
		{
			return $label;
		}

		// Recursively translate label array
		if (is_array($label))
		{
			foreach ($label as $k => $v)
			{
				$label[$k] = $this->translate($v, $language);
			}

			return $label;
		}

		if (!$language)
		{
			$language = $GLOBALS['TL_LANGUAGE'];
		}

		$this->import('String');

		if (!is_array($GLOBALS['ISO_LANG']['TBL'][$language]))
		{
			$GLOBALS['ISO_LANG']['TBL'][$language] = array();
			$objLabels = $this->Database->execute("SELECT * FROM tl_iso_labels WHERE language='$language'");

			while ($objLabels->next())
			{
				$GLOBALS['ISO_LANG']['TBL'][$language][$this->String->decodeEntities($objLabels->label)] = $objLabels->replacement;
			}
		}

		$label = $this->String->decodeEntities($label);
		return $GLOBALS['ISO_LANG']['TBL'][$language][$label] ? $GLOBALS['ISO_LANG']['TBL'][$language][$label] : $label;
	}


	/**
	 * Format value (based on DC_Table::show(), Contao 2.9.0)
	 * @param string
	 * @param string
	 * @param mixed
	 * @return string
	 */
	public function formatValue($strTable, $strField, $varValue)
	{
		$varValue = deserialize($varValue);

		if (!is_array($GLOBALS['TL_DCA'][$strTable]))
		{
			$this->loadDataContainer($strTable);
			$this->loadLanguageFile($strTable);
		}

		// Get field value
		if (strlen($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['foreignKey']))
		{
			$chunks = explode('.', $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['foreignKey']);
			$varValue = empty($varValue) ? array(0) : $varValue;
			$objKey = $this->Database->execute("SELECT " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id IN (" . implode(',', array_map('intval', (array)$varValue)) . ")");

			return implode(', ', $objKey->fetchEach('value'));
		}

		elseif (is_array($varValue))
		{
			foreach ($varValue as $kk => $vv)
			{
				$varValue[$kk] = $this->formatValue($strTable, $strField, $vv);
			}

			return implode(', ', $varValue);
		}

		elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['rgxp'] == 'date')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $varValue);
		}

		elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['rgxp'] == 'time')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $varValue);
		}

		elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['rgxp'] == 'datim' || in_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['flag'], array(5, 6, 7, 8, 9, 10)) || $strField == 'tstamp')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $varValue);
		}

		elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['multiple'])
		{
			return strlen($varValue) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
		}

		elseif ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType'] == 'textarea' && ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['allowHtml'] || $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['preserveTags']))
		{
			return specialchars($varValue);
		}

		elseif (is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference']))
		{
			return isset($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference'][$varValue]) ? ((is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference'][$varValue])) ? $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference'][$varValue][0] : $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['reference'][$varValue]) : $varValue;
		}

		return $varValue;
	}


	/**
	 * Format label (based on DC_Table::show(), Contao 2.9.0)
	 * @param string
	 * @param string
	 * @return string
	 */
	public function formatLabel($strTable, $strField)
	{
		if (!is_array($GLOBALS['TL_DCA'][$strTable]))
		{
			$this->loadDataContainer($strTable);
			$this->loadLanguageFile($strTable);
		}

		// Label
		if (count($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label']))
		{
			$strLabel = is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label']) ? $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label'][0] : $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label'];
		}

		else
		{
			$strLabel = is_array($GLOBALS['TL_LANG']['MSC'][$strField]) ? $GLOBALS['TL_LANG']['MSC'][$strField][0] : $GLOBALS['TL_LANG']['MSC'][$strField];
		}

		if (!strlen($strLabel))
		{
			$strLabel = $strField;
		}

		return $strLabel;
	}


	/**
	 * Merge media manager data from fallback and translated product data
	 * @param array
	 * @param array
	 * @return array
	 */
	public function mergeMediaData($arrCurrent, $arrParent)
	{
		if (is_array($arrParent) && count($arrParent))
		{
			$arrTranslate = array();

			// Create an array of images where key = image name
			foreach( $arrParent as $i => $image)
			{
				if ($image['translate'] != 'all')
				{
					$arrTranslate[$image['src']] = $image;
				}
			}

			if (is_array($arrCurrent) && count($arrCurrent))
			{
				foreach ($arrCurrent as $i => $image)
				{
					if (isset($arrTranslate[$image['src']]))
					{
						if ($arrTranslate[$image['src']]['translate'] == '')
						{
							$arrCurrent[$i] = $arrTranslate[$image['src']];
						}
						else
						{
							$arrCurrent[$i]['link'] = $arrTranslate[$image['src']]['link'];
							$arrCurrent[$i]['translate'] = $arrTranslate[$image['src']]['translate'];
						}

						unset($arrTranslate[$image['src']]);
					}
					elseif ($arrCurrent[$i]['translate'] != 'all')
					{
						unset($arrCurrent[$i]);
					}
				}

				// Add remaining parent image to the list
				if (count($arrTranslate))
				{
					$arrCurrent = array_merge($arrCurrent, array_values($arrTranslate));
				}

				$arrCurrent = array_values($arrCurrent);
			}
			else
			{
				$arrCurrent = array_values($arrTranslate);
			}
		}

		return $arrCurrent;
	}


	/**
	 * Return select statement to load product data including multilingual fields
	 * @deprecated Moved to static function IsotopeProduct::getProductStatement()
	 * @see	IsotopeProduct::getProductStatement()
	 */
	public function getProductSelect()
	{
		trigger_error('Using Isotope::getProductSelect() is deprecated. Please use IsotopeProduct::getProductStatement()', E_USER_NOTICE);
		return IsotopeProduct::getProductStatement();
	}


	/**
	 * These functions need to be public for Models to access them
	 */
	public function replaceInsertTags($strBuffer, $blnCache=false) { return parent::replaceInsertTags($strBuffer, $blnCache); }
	public function convertRelativeUrls($strContent, $strBase='', $blnHrefOnly=false) { return parent::convertRelativeUrls($strContent, $strBase, $blnHrefOnly); }
}

