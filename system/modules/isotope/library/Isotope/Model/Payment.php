<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Environment;
use Contao\FrontendUser;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Translation;

/**
 * Class Payment
 * Parent class for all payment gateway modules.
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $label
 * @property string $type
 * @property string $note
 * @property int    $new_order_status
 * @property string $price
 * @property int    $tax_class
 * @property string $trans_type
 * @property string $quantity_mode
 * @property int    $minimum_quantity
 * @property int    $maximum_quantity
 * @property float  $minimum_total
 * @property float  $maximum_total
 * @property array  $countries
 * @property array  $shipping_modules
 * @property array  $product_types
 * @property string $product_types_condition
 * @property array  $config_ids
 * @property bool   $guests
 * @property bool   $protected
 * @property array  $groups
 * @property bool   $debug
 * @property bool   $logging
 * @property bool   $enabled
 */
abstract class Payment extends TypeAgent implements IsotopePayment
{
    const QUANTITY_MODE_ITEMS = 'cart_items';
    const QUANTITY_MODE_PRODUCTS = 'cart_products';

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_payment';

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
     * @inheritdoc
     */
    public function __construct(\Database\Result $objResult = null)
    {
        parent::__construct($objResult);

        $this->arrData['allowed_cc_types'] = StringUtil::deserialize($this->arrData['allowed_cc_types'] ?? null);

        if (\is_array($this->arrData['allowed_cc_types'])) {
            $this->arrData['allowed_cc_types'] = array_intersect(static::getAllowedCCTypes(), $this->arrData['allowed_cc_types']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * Return true or false depending on availability of the payment method
     *
     * @return bool
     *
     * @todo must check availability for a specific product collection (and not hardcoded to the current cart)
     *
     * @throws \InvalidArgumentException on unknown quantity mode
     * @throws \UnexpectedValueException on unknown product types condition
     */
    public function isAvailable()
    {
        if (TL_MODE === 'BE') {
            return true;
        }

        if (!$this->enabled && true !== BE_USER_LOGGED_IN) {
            return false;
        }

        if (($this->guests && true === FE_USER_LOGGED_IN) || ($this->protected && true !== FE_USER_LOGGED_IN)) {
            return false;
        }

        if ($this->protected) {
            $arrGroups = StringUtil::deserialize($this->groups);

            if (!\is_array($arrGroups)
                || 0 === \count($arrGroups)
                || 0 === \count(array_intersect($arrGroups, FrontendUser::getInstance()->groups))
            ) {
                return false;
            }
        }

        if (($this->minimum_total > 0 && $this->minimum_total > Isotope::getCart()->getSubtotal())
            || ($this->maximum_total > 0 && $this->maximum_total < Isotope::getCart()->getSubtotal())
        ) {
            return false;
        }

        if ($this->minimum_quantity > 0 || $this->maximum_quantity > 0) {
            switch ($this->quantity_mode) {
                case static::QUANTITY_MODE_ITEMS:
                    $quantity =  Isotope::getCart()->sumItemsQuantity();
                    break;

                case static::QUANTITY_MODE_PRODUCTS:
                    $quantity =  Isotope::getCart()->countItems();
                    break;

                default:
                    throw new \InvalidArgumentException(sprintf('Unknown quantity mode "%s"', $this->quantity_mode));
            }

            if (($this->minimum_quantity > 0 && $this->minimum_quantity > $quantity)
                || ($this->maximum_quantity > 0 && $this->maximum_quantity < $quantity)
            ) {
                return false;
            }
        }

        $arrConfigs = StringUtil::deserialize($this->config_ids);
        if (\is_array($arrConfigs) && \count($arrConfigs) > 0 && !\in_array(Isotope::getConfig()->id, $arrConfigs)) {
            return false;
        }

        $arrCountries = StringUtil::deserialize($this->countries);

        if (\is_array($arrCountries) && \count($arrCountries) > 0
            && !\in_array(Isotope::getCart()->getBillingAddress()->country, $arrCountries, true)
        ) {
            return false;
        }

        $arrShippings = StringUtil::deserialize($this->shipping_modules);

        if (\is_array($arrShippings)
            && \count($arrShippings) > 0
            && ((!Isotope::getCart()->hasShipping() && !\in_array(-1, $arrShippings))
                || (Isotope::getCart()->hasShipping() &&
                    !\in_array(Isotope::getCart()->getShippingMethod()->getId(), $arrShippings)
                )
            )
        ) {
            return false;
        }

        $arrConfigTypes = StringUtil::deserialize($this->product_types);

        if (\is_array($arrConfigTypes) && \count($arrConfigTypes) > 0) {
            $arrItems = Isotope::getCart()->getItems();
            $arrItemTypes = array();

            foreach ($arrItems as $objItem) {
                if ($objItem->hasProduct()) {
                    $productType = $objItem->getProduct()->getType();
                    $arrItemTypes[] = null === $productType ? 0 : $productType->id;

                } elseif ('onlyAvailable' === $this->product_types_condition) {
                    // If one product in cart is not of given type, shipping method is not available
                    return false;
                }
            }

            $arrItemTypes = array_unique($arrItemTypes);

            switch ($this->product_types_condition) {
                case 'onlyAvailable':
                    if (\count(array_diff($arrItemTypes, $arrConfigTypes)) > 0) {
                        return false;
                    }
                    break;

                case 'notAvailable':
                    if (\count(array_intersect($arrConfigTypes, $arrItemTypes)) > 0) {
                        return false;
                    }
                    break;

                case 'oneAvailable':
                    if (\count(array_intersect($arrConfigTypes, $arrItemTypes)) == 0) {
                        return false;
                    }
                    break;

                case 'allAvailable':
                    if (\count(array_intersect($arrConfigTypes, $arrItemTypes)) != \count($arrConfigTypes)) {
                        return false;
                    }
                    break;

                default:
                    throw new \UnexpectedValueException(
                        'Unknown product type condition "' . $this->product_types_condition . '"'
                    );
            }
        }

        // !HOOK: modify if payment method is available
        if (isset($GLOBALS['ISO_HOOKS']['paymentAvailable']) && \is_array($GLOBALS['ISO_HOOKS']['paymentAvailable'])) {
            foreach ($GLOBALS['ISO_HOOKS']['paymentAvailable'] as $callback) {
                if (!System::importStatic($callback[0])->{$callback[1]}($this)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Return true if the payment has a percentage (not fixed) amount
     *
     * @return bool
     */
    public function isPercentage()
    {
        return '%' === substr($this->arrData['price'], -1);
    }

    /**
     * Return percentage amount (if applicable)
     *
     * @return float
     *
     * @throws \UnexpectedValueException if the surcharge is not a percentage amount.
     */
    public function getPercentage()
    {
        if (!$this->isPercentage()) {
            throw new \UnexpectedValueException('Payment method does not have a percentage amount.');
        }

        return (float) substr($this->arrData['price'], 0, -1);
    }

    /**
     * Return percentage label if price is percentage
     *
     * @return string
     */
    public function getPercentageLabel()
    {
        return $this->isPercentage() ? $this->arrData['price'] : '';
    }

    /**
     * Return calculated price for this payment method
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return float
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if ('' === (string) $this->arrData['price']) {
            return null;
        }

        if ($this->isPercentage()) {
            if (null === $objCollection) {
                $objCollection = Isotope::getCart();
            }

            $fltPrice = $objCollection->getSubtotal() / 100 * $this->getPercentage();
        } else {
            $fltPrice = (float) $this->arrData['price'];
        }

        return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
    }

    /**
     * Return translated label for this payment method
     *
     * @return string
     */
    public function getLabel()
    {
        return Translation::get($this->label ? : $this->name);
    }

    /**
     * @inheritdoc
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Return a html form for checkout or false
     *
     * @param IsotopeProductCollection $objOrder The order being places
     * @param Module $objModule The checkout module instance
     *
     * @return bool
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        return false;
    }

    /**
     * Return information or advanced features in the backend.
     * Use this function to present advanced features or basic payment information for an order in the backend.
     *
     * @param int $orderId
     *
     * @return string
     */
    protected function backendInterface($orderId)
    {
        return '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=payment', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'][$this->type][0] . ')' . '</h2>

<div id="tl_soverview">
<div id="tl_messages">
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
     * @inheritdoc
     */
    public function getSurcharge(IsotopeProductCollection $objCollection)
    {
        if (null === $this->getPrice()) {
            return null;
        }

        return ProductCollectionSurcharge::createForPaymentInCollection($this, $objCollection);
    }

    /**
     * Logs information for this payment method if enabled.
     *
     * @param mixed $value
     */
    protected function debugLog($value)
    {
        if (!$this->logging) {
            return;
        }

        $pos = strrpos(\get_called_class(), '\\') ?: -1;
        $className = substr(\get_called_class(), $pos+1);

        $logFile = sprintf(
            'isotope_%s-%s.log',
            strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), str_replace('_', '.', $className))),
            date('Y-m-d')
        );

        log_message(print_r($value, true), $logFile);
    }

    /**
     * Validate a credit card number and return the card type.
     * http://regexlib.com/UserPatterns.aspx?authorid=7128ecda-5ab1-451d-98d9-f94d2a453b37
     *
     * @param string $strNumber
     *
     * @return mixed
     */
    protected static function validateCreditCard($strNumber)
    {
        $strNumber = preg_replace('@[^0-9]+@', '', $strNumber);

        if (preg_match('@(^4\d{12}$)|(^4[0-8]\d{14}$)|(^(49)[^013]\d{13}$)|(^(49030)[0-1]\d{10}$)|(^(49033)[0-4]\d{10}$)|(^(49110)[^12]\d{10}$)|(^(49117)[0-3]\d{10}$)|(^(49118)[^0-2]\d{10}$)|(^(493)[^6]\d{12}$)@', $strNumber)) {
            return 'visa';
        }

        if (preg_match('@(^(5[0678])\d{11,18}$) |(^(6[^0357])\d{11,18}$) |(^(601)[^1]\d{9,16}$) |(^(6011)\d{9,11}$) |(^(6011)\d{13,16}$) |(^(65)\d{11,13}$) |(^(65)\d{15,18}$) |(^(633)[^34](\d{9,16}$)) |(^(6333)[0-4](\d{8,10}$)) |(^(6333)[0-4](\d{12}$)) |(^(6333)[0-4](\d{15}$)) |(^(6333)[5-9](\d{8,10}$)) |(^(6333)[5-9](\d{12}$)) |(^(6333)[5-9](\d{15}$)) |(^(6334)[0-4](\d{8,10}$)) |(^(6334)[0-4](\d{12}$)) |(^(6334)[0-4](\d{15}$)) |(^(67)[^(59)](\d{9,16}$)) |(^(6759)](\d{9,11}$)) |(^(6759)](\d{13}$)) |(^(6759)](\d{16}$)) |(^(67)[^(67)](\d{9,16}$)) |(^(6767)](\d{9,11}$)) |(^(6767)](\d{13}$)) |(^(6767)](\d{16}$))@', $strNumber)) {
            return 'maestro';
        }

        if (preg_match('@^5[1-5]\d{14}$@', $strNumber)) {
            return 'mc';
        }

        if (preg_match('@(^(6011)\d{12}$)|(^(65)\d{14}$)@', $strNumber)) {
            return 'discover';
        }

        if (preg_match('@(^3[47])((\d{11}$)|(\d{13}$))@', $strNumber)) {
            return 'amex';
        }

        if (preg_match('@(^(6334)[5-9](\d{11}$|\d{13,14}$)) |(^(6767)(\d{12}$|\d{14,15}$))@', $strNumber)) {
            return 'solo';
        }

        if (preg_match('@(^(49030)[2-9](\d{10}$|\d{12,13}$)) |(^(49033)[5-9](\d{10}$|\d{12,13}$)) |(^(49110)[1-2](\d{10}$|\d{12,13}$)) |(^(49117)[4-9](\d{10}$|\d{12,13}$)) |(^(49118)[0-2](\d{10}$|\d{12,13}$)) |(^(4936)(\d{12}$|\d{14,15}$)) |(^(564182)(\d{11}$|\d{13,14}$)) |(^(6333)[0-4](\d{11}$|\d{13,14}$)) |(^(6759)(\d{12}$|\d{14,15}$))@', $strNumber)) {
            return 'switch';
        }

        if (preg_match('@(^(352)[8-9](\d{11}$|\d{12}$))|(^(35)[3-8](\d{12}$|\d{13}$))@', $strNumber)) {
            return 'jcb';
        }

        if (preg_match('@(^(30)[0-5]\d{11}$)|(^(36)\d{12}$)|(^(38[0-8])\d{11}$)@', $strNumber)) {
            return 'diners';
        }

        if (preg_match('@^(389)[0-9]{11}$@', $strNumber)) {
            return 'cartblanche';
        }

        if (preg_match('@(^(2014)|^(2149))\d{11}$@', $strNumber)) {
            return 'enroute';
        }

        if (preg_match('@(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))@', $strNumber)) {
            return 'ukdebit';
        }

        return false;
    }

    /**
     * Return a list of valid credit card types for this payment module
     *
     * @return array
     *
     * @deprecated Deprecated since 2.2, to be removed in 3.0. Create your own DCA field instead.
     */
    public static function getAllowedCCTypes()
    {
        return array();
    }
}
