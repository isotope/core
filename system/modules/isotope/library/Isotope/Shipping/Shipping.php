<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Shipping;


/**
 * Class Shipping
 *
 * Parent class for all shipping gateway modules
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class Shipping extends \Frontend
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate;

    /**
     * Current record
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
     * @param array
     */
    public function __construct($arrRow)
    {
        parent::__construct();

        $this->import('Isotope\Isotope', 'Isotope');
        $this->arrData = $arrRow;
    }


    /**
     * Set an object property
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        $this->arrData[$strKey] = $varValue;
    }


    /**
     * Return an object property
     * @param string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'label':
                return $this->Isotope->translate($this->arrData['label'] ? $this->arrData['label'] : $this->arrData['name']);
                break;

            case 'available':
                if (!$this->enabled && BE_USER_LOGGED_IN !== true)
                {
                    return false;
                }

                if (($this->guests && FE_USER_LOGGED_IN === true) || ($this->protected && FE_USER_LOGGED_IN !== true))
                {
                    return false;
                }

                if ($this->protected)
                {
                    $this->import('FrontendUser', 'User');
                    $arrGroups = deserialize($this->groups);

                    if (!is_array($arrGroups) || empty($arrGroups) || !count(array_intersect($arrGroups, $this->User->groups)))
                    {
                        return false;
                    }
                }

                if (($this->minimum_total > 0 && $this->minimum_total > $this->Isotope->Cart->subTotal) || ($this->maximum_total > 0 && $this->maximum_total < $this->Isotope->Cart->subTotal))
                {
                    return false;
                }

                $objAddress = $this->Isotope->Cart->shippingAddress;

                $arrCountries = deserialize($this->countries);
                if (is_array($arrCountries) && !empty($arrCountries) && !in_array($objAddress->country, $arrCountries))
                {
                    return false;
                }

                $arrSubdivisions = deserialize($this->subdivisions);
                if (is_array($arrSubdivisions) && !empty($arrSubdivisions) && !in_array($objAddress->subdivision, $arrSubdivisions))
                {
                    return false;
                }

                // Check if address has a valid postal code
                if ($this->postalCodes != '')
                {
                    $arrCodes = \Isotope\Frontend::parsePostalCodes($this->postalCodes);

                    if (!in_array($objAddress->postal, $arrCodes))
                    {
                        return false;
                    }
                }

                $arrTypes = deserialize($this->product_types);

                if (is_array($arrTypes) && !empty($arrTypes))
                {
                    $arrProducts = $this->Isotope->Cart->getProducts();

                    foreach ($arrProducts as $objProduct)
                    {
                        if (!in_array($objProduct->type, $arrTypes))
                        {
                            return false;
                        }
                    }
                }

                return true;
                break;

            case 'price':
                $strPrice = $this->arrData['price'];
                $blnPercentage = substr($strPrice, -1) == '%' ? true : false;

                if ($blnPercentage)
                {
                    $fltSurcharge = (float) substr($strPrice, 0, -1);
                    $fltPrice = $this->Isotope->Cart->subTotal / 100 * $fltSurcharge;
                }
                else
                {
                    $fltPrice = (float) $strPrice;
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
     */
    public function moduleOptionsLoad() {}


    /**
     * List module options in backend
     * @param array
     * @return string
     */
    public function moduleOptionsList($row)
    {
        return $row['name'];
    }


    /**
     * Return information or advanced features in the backend.
     * Use this function to present advanced features or basic shipping information for an order in the backend.
     * @param integer
     * @return string
     */
    public function backendInterface($orderId)
    {
        return '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=shipping', '', Environment::get('request'))) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
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
     */
    public function processPostSale() {}


    /**
     * This function is used to gather any addition shipping options that might be available specific to the current customer or order.
     * For example, expedited shipping based on customer location.
     * @param object
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
     * @return string
     */
    public function checkoutReview()
    {
        return $this->label;
    }


    /**
     * Get the checkout surcharge for this shipping method
     */
    public function getSurcharge($objCollection)
    {
        if ($this->arrData['price'] == 0)
        {
            return false;
        }

        return $this->Isotope->calculateSurcharge(
                                $this->arrData['price'],
                                ($GLOBALS['TL_LANG']['MSC']['shippingLabel'] . ' (' . $this->label . ')'),
                                $this->arrData['tax_class'],
                                $objCollection->getProducts(),
                                $this);
    }


    /**
     * Return the name and description for this shipping method
     * @return array
     */
    public static function getLabel()
    {
        return $GLOBALS['ISO_LANG']['SHIP'][strtolower(str_replace('Isotope\Shipping\\', '', get_called_class()))];
    }
}
