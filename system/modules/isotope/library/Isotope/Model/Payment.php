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


/**
 * Class Payment
 *
 * Parent class for all payment gateway modules.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class Payment extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_payment_modules';

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
            case 'label':
                return Isotope::translate($this->arrData['label'] ? $this->arrData['label'] : $this->arrData['name']);
                break;

            case 'available':
                throw new BadFunctionCallException('Your payment method does not work with Isotope 2.x');
                break;

            case 'price':
                $strPrice = $this->arrData['price'];
                $blnPercentage = substr($strPrice, -1) == '%' ? true : false;

                if ($blnPercentage)
                {
                    $fltSurcharge = (float) substr($strPrice, 0, -1);
                    $fltPrice = Isotope::getCart()->subTotal / 100 * $fltSurcharge;
                }
                else
                {
                    $fltPrice = (float) $strPrice;
                }

                return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
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
            $this->import('FrontendUser', 'User');
            $arrGroups = deserialize($this->groups);

            if (!is_array($arrGroups) || empty($arrGroups) || !count(array_intersect($arrGroups, $this->User->groups))) // Can't use empty() because its an object property (using __get)
            {
                return false;
            }
        }

        if (($this->minimum_total > 0 && $this->minimum_total > Isotope::getCart()->subTotal) || ($this->maximum_total > 0 && $this->maximum_total < Isotope::getCart()->subTotal))
        {
            return false;
        }

        $arrCountries = deserialize($this->countries);

        if(is_array($arrCountries) && !empty($arrCountries) && !in_array(Isotope::getCart()->billingAddress->country, $arrCountries))
        {
            return false;
        }

        $arrShippings = deserialize($this->shipping_modules);

        if (is_array($arrShippings) && !empty($arrShippings) && ((!Isotope::getCart()->hasShipping() && !in_array(-1, $arrShippings)) || (Isotope::getCart()->hasShipping() && !in_array(Isotope::getCart()->Shipping->id, $arrShippings))))
        {
            return false;
        }

        $arrTypes = deserialize($this->product_types);

        if (is_array($arrTypes) && !empty($arrTypes))
        {
            $arrProducts = Isotope::getCart()->getProducts();

            foreach ($arrProducts as $objProduct)
            {
                if (!in_array($objProduct->type, $arrTypes))
                {
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
     * Process post-sale requests. Does nothing by default.
     *
     * This function can be called from the postsale.php file when the payment server is requestion/posting a status change.
     * You can see an implementation example in PaymentPostfinance.php
     */
    public function processPostSale() {}


    /**
     * Return a html form for payment data or an empty string.
     *
     * The input fields should be from array "payment" including the payment module ID.
     * Example: <input type="text" name="payment[$this->id][cc_num]" />
     * Post-Value "payment" is automatically stored in $_SESSION['CHECKOUT_DATA']['payment']
     * You can set $objCheckoutModule->doNotSubmit = true if post is sent but data is invalid.
     *
     * @param object The checkout module object.
     * @return string
     */
    public function paymentForm($objCheckoutModule)
    {
        return '';
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

        return Isotope::calculateSurcharge(
                   $this->arrData['price'],
                   ($GLOBALS['TL_LANG']['MSC']['paymentLabel'] . ' (' . $this->label . ')'),
                   $this->arrData['tax_class'],
                   $objCollection->getProducts(),
                   $this);
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


    /**
     * Override parent addToUrl function. Use generateFrontendUrl if we want to remove all parameters.
     * @param string
     * @param boolean
     * @return string
     */
/*
    protected function addToUrl($strRequest, $blnIgnoreParams=false)
    {
        if ($blnIgnoreParams)
        {
            global $objPage;

            // Support for auto_item parameter
            if ($GLOBALS['TL_CONFIG']['useAutoItem'])
            {
                $strRequest = str_replace('step=', '', $strRequest);
            }

            return $this->generateFrontendUrl($objPage->row(), '/' . str_replace(array('=', '&amp;', '&'), '/', $strRequest));
        }

        return parent::addToUrl($strRequest, $blnIgnoreParams);
    }
*/


    /**
     * Return the name and description for this payment method
     * @return array
     */
    public static function getClassLabel()
    {
        return $GLOBALS['TL_LANG']['PAY'][strtolower(str_replace('Isotope\Model\Payment\\', '', get_called_class()))];
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
            $strClass = '\Isotope\Model\Payment\\' . $objResult->type;

            return new $strClass($objResult);
        } else {

            return new \Isotope\Model\Collection\Payment($objResult, static::$strTable);
        }
    }
}
