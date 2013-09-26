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
 * Class Payment
 *
 * Parent class for all payment gateway modules.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class Payment extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_payment_modules';

    /**
     * Interface to validate payment method
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopePayment';

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
     * Initialize the object
     * @param array
     */
    public function __construct(\Database\Result $objResult=null)
    {
        parent::__construct($objResult);

        $this->arrData['allowed_cc_types'] = deserialize($this->arrData['allowed_cc_types']);

        if (is_array($this->arrData['allowed_cc_types']))
        {
            $this->arrData['allowed_cc_types'] = array_intersect(static::getAllowedCCTypes(), $this->arrData['allowed_cc_types']);
        }
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
            case 'available':
                throw new \BadFunctionCallException('Your payment method does not work with Isotope 2.x');
                break;

            case 'surcharge':
                return substr($this->arrData['price'], -1) == '%' ? $this->arrData['price'] : '';
                break;

            default:
                return parent::__get($strKey);
        }
    }


    /**
     * Return true or false depending on availability of the payment method
     * @return bool
     */
    public function isAvailable()
    {
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
            $arrGroups = deserialize($this->groups);

            if (!is_array($arrGroups) || empty($arrGroups) || !count(array_intersect($arrGroups, \FrontendUser::getInstance()->groups))) // Can't use empty() because its an object property (using __get)
            {
                return false;
            }
        }

        if (($this->minimum_total > 0 && $this->minimum_total > Isotope::getCart()->getSubtotal()) || ($this->maximum_total > 0 && $this->maximum_total < Isotope::getCart()->getSubtotal()))
        {
            return false;
        }

        $arrCountries = deserialize($this->countries);

        if (is_array($arrCountries) && !empty($arrCountries) && !in_array(Isotope::getCart()->getBillingAddress()->country, $arrCountries))
        {
            return false;
        }

        $arrShippings = deserialize($this->shipping_modules);

        if (is_array($arrShippings) && !empty($arrShippings) && ((!Isotope::getCart()->hasShipping() && !in_array(-1, $arrShippings)) || (Isotope::getCart()->hasShipping() && !in_array(Isotope::getCart()->getShippingMethod()->id, $arrShippings))))
        {
            return false;
        }

        $arrTypes = deserialize($this->product_types);

        if (is_array($arrTypes) && !empty($arrTypes))
        {
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
     * Return true if the payment has a percentage (not fixed) amount
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
            throw new \UnexpectedValueException('Payment method does not have a percentage amount.');
        }

        return (float) substr($this->arrData['price'], 0, -1);
    }


    /**
     * Return calculated price for this payment method
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
     * Return translated label for this payment method
     * @return string
     */
    public function getLabel()
    {
        return Translation::get($this->label ?: $this->name);
    }


    /**
     * Return a html form for checkout or false
     * @return mixed
     */
    public function checkoutForm()
    {
        return false;
    }


    /**
     * Return information or advanced features in the backend.
     *
     * Use this function to present advanced features or basic payment information for an order in the backend.
     * @param integer Order ID
     * @return string
     */
    public function backendInterface($orderId)
    {
        return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=payment', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['PAY'][$this->type][0] . ')' . '</h2>

<div class="tl_formbody_edit">
<div class="tl_tbox block">
<p class="tl_info">' . $GLOBALS['TL_LANG']['MSC']['backendPaymentNoInfo'] . '</p>
</div>
</div>';
    }


    /**
     * Return the checkout review information.
     *
     * Use this to return custom checkout information about this payment module.
     * Example: parial information about the used credit card.
     *
     * @return string
     */
    public function checkoutReview()
    {
        return $this->getLabel();
    }


    /**
     * Get the checkout surcharge for this payment method
     * @return  Isotope\Model\ProductCollectionSurcharge\Payment|null
     */
    public function getSurcharge($objCollection)
    {
        if ($this->getPrice() == 0)
        {
            return null;
        }

        return ProductCollectionSurcharge::createForPaymentInCollection($this, $objCollection);
    }


    /**
     * Validate a credit card number and return the card type.
     * http://regexlib.com/UserPatterns.aspx?authorid=7128ecda-5ab1-451d-98d9-f94d2a453b37
     *
     * @param string
     * @return mixed
     */
    protected static function validateCreditCard($strNumber)
    {
        $strNumber = preg_replace('@[^0-9]+@', '', $strNumber);

        if (preg_match('@(^4\d{12}$)|(^4[0-8]\d{14}$)|(^(49)[^013]\d{13}$)|(^(49030)[0-1]\d{10}$)|(^(49033)[0-4]\d{10}$)|(^(49110)[^12]\d{10}$)|(^(49117)[0-3]\d{10}$)|(^(49118)[^0-2]\d{10}$)|(^(493)[^6]\d{12}$)@', $strNumber))
        {
            return 'visa';
        }
        elseif (preg_match('@(^(5[0678])\d{11,18}$) |(^(6[^0357])\d{11,18}$) |(^(601)[^1]\d{9,16}$) |(^(6011)\d{9,11}$) |(^(6011)\d{13,16}$) |(^(65)\d{11,13}$) |(^(65)\d{15,18}$) |(^(633)[^34](\d{9,16}$)) |(^(6333)[0-4](\d{8,10}$)) |(^(6333)[0-4](\d{12}$)) |(^(6333)[0-4](\d{15}$)) |(^(6333)[5-9](\d{8,10}$)) |(^(6333)[5-9](\d{12}$)) |(^(6333)[5-9](\d{15}$)) |(^(6334)[0-4](\d{8,10}$)) |(^(6334)[0-4](\d{12}$)) |(^(6334)[0-4](\d{15}$)) |(^(67)[^(59)](\d{9,16}$)) |(^(6759)](\d{9,11}$)) |(^(6759)](\d{13}$)) |(^(6759)](\d{16}$)) |(^(67)[^(67)](\d{9,16}$)) |(^(6767)](\d{9,11}$)) |(^(6767)](\d{13}$)) |(^(6767)](\d{16}$))@', $strNumber))
        {
            return 'maestro';
        }
        elseif (preg_match('@^5[1-5]\d{14}$@', $strNumber))
        {
            return 'mc';
        }
        elseif (preg_match('@(^(6011)\d{12}$)|(^(65)\d{14}$)@', $strNumber))
        {
            return 'discover';
        }
        elseif (preg_match('@(^3[47])((\d{11}$)|(\d{13}$))@', $strNumber))
        {
            return 'amex';
        }
        elseif (preg_match('@(^(6334)[5-9](\d{11}$|\d{13,14}$)) |(^(6767)(\d{12}$|\d{14,15}$))@', $strNumber))
        {
            return 'solo';
        }
        elseif (preg_match('@(^(49030)[2-9](\d{10}$|\d{12,13}$)) |(^(49033)[5-9](\d{10}$|\d{12,13}$)) |(^(49110)[1-2](\d{10}$|\d{12,13}$)) |(^(49117)[4-9](\d{10}$|\d{12,13}$)) |(^(49118)[0-2](\d{10}$|\d{12,13}$)) |(^(4936)(\d{12}$|\d{14,15}$)) |(^(564182)(\d{11}$|\d{13,14}$)) |(^(6333)[0-4](\d{11}$|\d{13,14}$)) |(^(6759)(\d{12}$|\d{14,15}$))@', $strNumber))
        {
            return 'switch';
        }
        elseif (preg_match('@(^(352)[8-9](\d{11}$|\d{12}$))|(^(35)[3-8](\d{12}$|\d{13}$))@', $strNumber))
        {
            return 'jcb';
        }
        elseif (preg_match('@(^(30)[0-5]\d{11}$)|(^(36)\d{12}$)|(^(38[0-8])\d{11}$)@', $strNumber))
        {
            return 'diners';
        }
        elseif (preg_match('@^(389)[0-9]{11}$@', $strNumber))
        {
            return 'cartblanche';
        }
        elseif (preg_match('@(^(2014)|^(2149))\d{11}$@', $strNumber))
        {
            return 'enroute';
        }
        elseif (preg_match('@(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))@', $strNumber))
        {
            return 'ukdebit';
        }

        return false;
    }


    /**
     * Return a list of valid credit card types for this payment module
     * @return array
     */
    public static function getAllowedCCTypes()
    {
        return array();
    }
}
