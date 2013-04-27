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
use Isotope\Factory\ProductCollectionSurcharge as SurchargeFactory;
use Isotope\Interfaces\IsotopeProductCollection;


/**
 * Class Shipping
 *
 * Parent class for all shipping gateway modules
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class Shipping extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_shipping_modules';

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
                throw new BadFunctionCallException('Your shipping method does not work with Isotope 2.x');
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

            if (!is_array($arrGroups) || empty($arrGroups) || !count(array_intersect($arrGroups, FrontendUser::getInstance()->groups))) {
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
            $arrProducts = Isotope::getCart()->getProducts();

            foreach ($arrProducts as $objProduct) {
                if (!in_array($objProduct->type, $arrTypes)) {
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
        substr($this->arrData['price'], -1) == '%' ? true : false;
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
        return Isotope::translate($this->arrData['label'] ? $this->arrData['label'] : $this->arrData['name']);
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
        if ($this->getPrice() == 0)
        {
            return false;
        }

        return SurchargeFactory::buildShippingSurcharge($this, $objCollection);
    }


    /**
     * Return the name and description for this shipping method
     * @return array
     */
    public static function getClassLabel()
    {
        return $GLOBALS['TL_LANG']['SHIP'][strtolower(str_replace('Isotope\Model\Shipping\\', '', get_called_class()))];
    }


    /**
     * Return a model or collection based on the database result type
     */
    protected static function find(array $arrOptions)
    {
        if (static::$strTable == '')
        {
            return null;
        }

        $arrOptions['table'] = static::$strTable;
        $strQuery = \Model\QueryBuilder::find($arrOptions);

        $objStatement = \Database::getInstance()->prepare($strQuery);

        // Defaults for limit and offset
        if (!isset($arrOptions['limit']))
        {
            $arrOptions['limit'] = 0;
        }
        if (!isset($arrOptions['offset']))
        {
            $arrOptions['offset'] = 0;
        }

        // Limit
        if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0)
        {
            $objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
        }

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute($arrOptions['value']);

        if ($objResult->numRows < 1)
        {
            return null;
        }

        $objResult = static::postFind($objResult);

        if ($arrOptions['return'] == 'Model') {
            $strClass = '\Isotope\Model\Shipping\\' . $objResult->type;

            return new $strClass($objResult);
        } else {

            return new \Isotope\Model\Collection\Shipping($objResult, static::$strTable);
        }
    }
}
