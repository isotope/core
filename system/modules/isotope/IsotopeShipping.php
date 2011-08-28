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


/**
 * Parent class for all shipping gateway modules
 */
abstract class IsotopeShipping extends Frontend
{

	/**
	 * Template
	 *
	 * @access protected
	 * @var string
	 */
	protected $strTemplate;

	/**
	 * Current record
	 *
	 * @access protected
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;


	/**
	 * Initialize the object
	 *
	 * @access public
	 * @param array $arrRow
	 */
	public function __construct($arrRow)
	{
		parent::__construct();

		$this->import('Isotope');

		$this->arrData = $arrRow;
	}


	/**
	 * Set an object property
	 *
	 * @access public
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}


	/**
	 * Return an object property
	 *
	 * @access public
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'label':
				return $this->Isotope->translate($this->arrData['label'] ? $this->arrData['label'] : $this->arrData['name']);
				break;

			case 'available':
				if (!$this->enabled && !BE_USER_LOGGED_IN)
					return false;

				if (($this->guests && FE_USER_LOGGED_IN) || ($this->protected && !FE_USER_LOGGED_IN))
					return false;

				if ($this->protected)
				{
					$this->import('FrontendUser', 'User');
					$arrGroups = deserialize($this->groups);
					if (!is_array($arrGroups) || !count($arrGroups) || !count(array_intersect($arrGroups, $this->User->groups)))
						return false;
				}

				if (($this->minimum_total > 0 && $this->minimum_total > $this->Isotope->Cart->subTotal) || ($this->maximum_total > 0 && $this->maximum_total < $this->Isotope->Cart->subTotal))
					return false;

				$arrCountries = deserialize($this->countries);
				if(is_array($arrCountries) && count($arrCountries) && !in_array($this->Isotope->Cart->shippingAddress['country'], $arrCountries))
					return false;

				$arrSubdivisions = deserialize($this->subdivisions);
				// @todo this should be dropped with Contao 2.9 as all countries "should" have subdivisions
				$blnHasSubdivision = is_array($GLOBALS['TL_LANG']['DIV'][$this->Isotope->Cart->shippingAddress['country']]);

				if(is_array($arrSubdivisions) && count($arrSubdivisions) && !in_array($this->Isotope->Cart->shippingAddress['subdivision'], $arrSubdivisions) && $blnHasSubdivision)
					return false;

				$arrTypes = deserialize($this->product_types);
				if (is_array($arrTypes) && count($arrTypes))
				{
					$arrProducts = $this->Isotope->Cart->getProducts();
					foreach( $arrProducts as $objProduct )
					{
						if (!in_array($objProduct->type, $arrTypes))
							return false;
					}
				}

				return true;
				break;

			case 'price':
				$strPrice = $this->arrData['price'];
				$blnPercentage = substr($strPrice, -1) == '%' ? true : false;

				if ($blnPercentage)
				{
					$fltSurcharge = (float)substr($strPrice, 0, -1);
					$fltPrice = $this->Isotope->Cart->subTotal / 100 * $fltSurcharge;
				}
				else
				{
					$fltPrice = (float)$strPrice;
				}

				return $this->Isotope->calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
				break;

			case 'surcharge':
				return substr($this->arrData['price'], -1) == '%' ? $this->arrData['price'] : '';
				break;
		}

		return $this->arrData[$strKey];
	}


	/**
	 * Check whether a property is set
	 * @param string
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
	}


	/**
	 * Initialize the module options DCA in backend
	 *
	 * @access public
	 * @return string
	 */
	public function moduleOptionsLoad() {}


	/**
	 * List module options in backend
	 *
	 * @access public
	 * @return string
	 */
	public function moduleOptionsList($row)
	{
		return $row['name'];
	}


	/**
	 * Return information or advanced features in the backend.
	 *
	 * Use this function to present advanced features or basic shipping information for an order in the backend.
	 *
	 * @access public
	 * @param  int		Order ID
	 * @return string
	 */
	public function backendInterface($orderId)
	{
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=shipping', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['ISO_LANG']['SHIP'][$this->type][0] . ')' . '</h2>

<div class="tl_formbody_edit">
<div class="tl_tbox block">
<p class="tl_info">' . $GLOBALS['TL_LANG']['ISO']['backendShippingNoInfo'] . '</p>
</div>
</div>';
	}


	/**
	 * Process post-sale requests. Does nothing by default.
	 *
	 * This function can be called from the postsale.php file when the shipping server is requestion/posting a status change.
	 * You can see an implementation example in PaymentPostfinance.php
	 *
	 * @abstract
	 * @access public
	 * @return void
	 */
	public function processPostSale() {}


	/**
	 * This function is used to gather any addition shipping options that might be available specific to the current customer or order.
	 * For example, expedited shipping based on customer location.
	 *
	 * @access public
	 * @param  object
	 * @return string
	 */
	public function getShippingOptions(&$objModule)
	{
		return '';
	}


	/**
	 * Return the checkout review information.
	 *
	 * Use this to return custom checkout information about this shipping module.
	 * Example: Information about tracking codes.
	 *
	 * @access public
	 * @return string
	 */
	public function checkoutReview()
	{
		return $this->label;
	}
}

