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

namespace Isotope\Model;

use Isotope\Isotope;
use Isotope\Translation;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollectionSurcharge;


/**
 * Class Shipping
 *
 * Parent class for all shipping gateway modules
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class Shipping extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_shipping_modules';

    /**
     * Interface to validate shipping method
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeShipping';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

    /**
     * Template
     * @var string
     */
    protected $strTemplate;


    /**
     * Return an object property
     * @param string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'available':
                throw new \BadFunctionCallException('Your shipping method does not work with Isotope 2.x');
                break;

            case 'surcharge':
                return substr($this->arrData['price'], -1) == '%' ? $this->arrData['price'] : '';
                break;

            default:
                return parent::__get($strKey);
        }
    }


    /**
     * Return true or false depending on if shipping method is available
     * @return bool
     */
    public function isAvailable()
    {
        if (!$this->enabled && BE_USER_LOGGED_IN !== true) {
            return false;
        }

        if (($this->guests && FE_USER_LOGGED_IN === true) || ($this->protected && FE_USER_LOGGED_IN !== true)) {
            return false;
        }

        if ($this->protected) {
            $arrGroups = deserialize($this->groups);

            if (!is_array($arrGroups) || empty($arrGroups) || !count(array_intersect($arrGroups, \FrontendUser::getInstance()->groups))) {
                return false;
            }
        }

        if (($this->minimum_total > 0 && $this->minimum_total > Isotope::getCart()->getSubtotal()) || ($this->maximum_total > 0 && $this->maximum_total < Isotope::getCart()->getSubtotal())) {
            return false;
        }

        $objAddress = Isotope::getCart()->getShippingAddress();

        $arrCountries = deserialize($this->countries);
        if (is_array($arrCountries) && !empty($arrCountries) && !in_array($objAddress->country, $arrCountries)) {
            return false;
        }

        $arrSubdivisions = deserialize($this->subdivisions);
        if (is_array($arrSubdivisions) && !empty($arrSubdivisions) && !in_array($objAddress->subdivision, $arrSubdivisions)) {
            return false;
        }

        // Check if address has a valid postal code
        if ($this->postalCodes != '') {
            $arrCodes = \Isotope\Frontend::parsePostalCodes($this->postalCodes);

            if (!in_array($objAddress->postal, $arrCodes)) {
                return false;
            }
        }

        $arrTypes = deserialize($this->product_types);

        if (is_array($arrTypes) && !empty($arrTypes)) {
            $arrItems = Isotope::getCart()->getItems();

            foreach ($arrItems as $objItem) {
                if (!$objItem->hasProduct() || !in_array($objItem->getProduct()->type, $arrTypes)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Return true if the shipping has a percentage (not fixed) amount
     * @return bool
     */
    public function isPercentage()
    {
        return (substr($this->arrData['price'], -1) == '%') ? true : false;
    }


    /**
     * Return percentage amount (if applicable)
     * @return float
     * @throws UnexpectedValueException
     */
    public function getPercentage()
    {
        if (!$this->isPercentage())
        {
            throw new \UnexpectedValueException('Shipping method does not have a percentage amount.');
        }

        return (float) substr($this->arrData['price'], 0, -1);
    }


    /**
     * Return calculated price for this shipping method
     * @return float
     */
    public function getPrice(IsotopeProductCollection $objCollection=null)
    {
        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        if ($this->isPercentage()) {
            $fltPrice = $objCollection->getSubtotal() / 100 * $this->getPercentage();
        } else {
            $fltPrice = (float) $this->arrData['price'];
        }

        return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
    }


    /**
     * Return translated label for this shipping method
     * @return string
     */
    public function getLabel()
    {
        return Translation::get($this->label ?: $this->name);
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
<a href="' . ampersand(str_replace('&key=shipping', '', \Environment::get('request'))) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['SHIP'][$this->type][0] . ')' . '</h2>

<div class="tl_formbody_edit">
<div class="tl_tbox block">
<p class="tl_info">' . $GLOBALS['TL_LANG']['MSC']['backendShippingNoInfo'] . '</p>
</div>
</div>';
    }


    /**
     * Process post-sale requests. Does nothing by default.
     *
     * This function can be called from the postsale.php file when the shipping server is requestion/posting a status change.
     * You can see an implementation example in PaymentPostfinance.php
     */
    public function processPostsale() {}


    /**
     * Return the checkout review information.
     *
     * Use this to return custom checkout information about this shipping module.
     * Example: Information about tracking codes.
     * @return string
     */
    public function checkoutReview()
    {
        return $this->getLabel();
    }


    /**
     * Get the checkout surcharge for this shipping method
     */
    public function getSurcharge(IsotopeProductCollection $objCollection)
    {
        if ($this->getPrice() == 0)
        {
            return null;
        }

        return ProductCollectionSurcharge::createForShippingInCollection($this, $objCollection);
    }
}
