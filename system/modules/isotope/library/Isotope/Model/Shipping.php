<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Haste\Units\Mass\Weight;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Translation;


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
    protected static $strTable = 'tl_iso_shipping';

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
     * Return true or false depending on if shipping method is available
     * @return bool
     * @todo must check availability for a specific product collection (and not hardcoded to the current cart)
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

        $objScale = Isotope::getCart()->addToScale();

        if (($minWeight = Weight::createFromTimePeriod($this->minimum_weight)) !== null && $objScale->isLessThan($minWeight)) {
            return false;
        }

        if (($maxWeight = Weight::createFromTimePeriod($this->maximum_weight)) !== null && $objScale->isMoreThan($maxWeight)) {
            return false;
        }

        $arrConfigs = deserialize($this->config_ids);
        if (is_array($arrConfigs) && !empty($arrConfigs) && !in_array(Isotope::getConfig()->id, $arrConfigs)) {
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
     * @throws \UnexpectedValueException
     */
    public function getPercentage()
    {
        if (!$this->isPercentage()) {
            throw new \UnexpectedValueException('Shipping method does not have a percentage amount.');
        }

        return (float) substr($this->arrData['price'], 0, -1);
    }

    /**
     * Return percentage label if price is percentage
     * @return  string
     */
    public function getPercentageLabel()
    {
        return $this->isPercentage() ? $this->arrData['price'] : '';
    }


    /**
     * Return calculated price for this shipping method
     * @return float
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
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
        return Translation::get($this->label ? : $this->name);
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

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping.'.$this->type][0] . ')' . '</h2>

<div class="tl_formbody_edit">
<div class="tl_tbox block">
<p class="tl_info">' . $GLOBALS['TL_LANG']['MSC']['backendShippingNoInfo'] . '</p>
</div>
</div>';
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
        return $this->getLabel();
    }


    /**
     * Get the checkout surcharge for this shipping method
     */
    public function getSurcharge(IsotopeProductCollection $objCollection)
    {
        if ($this->getPrice() == 0) {
            return null;
        }

        return ProductCollectionSurcharge::createForShippingInCollection($this, $objCollection);
    }
}
