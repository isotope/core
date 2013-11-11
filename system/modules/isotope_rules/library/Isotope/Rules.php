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

namespace Isotope;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Rule;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollectionSurcharge\Rule as RuleSurcharge;


/**
 * Class Rules
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Rules extends \Controller
{

    /**
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstance;

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

        // User object must be loaded from cart, e.g. for postsale handling
        if (Isotope::getCart()->member > 0) {
            $this->User = \Database::getInstance()->prepare("SELECT * FROM tl_member WHERE id=?")->execute(Isotope::getCart()->member);
        }
    }


    /**
     * Instantiate the singleton if necessary and return it
     * @return object
     */
    public static function getInstance()
    {
        if (!is_object(static::$objInstance)) {
            static::$objInstance = new \Isotope\Rules();
        }

        return static::$objInstance;
    }


    /**
     * Calculate the price for a product, applying rules and coupons
     *
     * @param    float
     * @param    object
     * @param    string
     * @param    int
     * @return float
     */
    public function calculatePrice($fltPrice, $objSource, $strField, $intTaxClass)
    {
        if ($objSource instanceof IsotopeProduct && ($strField == 'price' || $strField == 'low_price'))
        {
            $objRules = Rule::findByProduct($objSource, $strField, $fltPrice);

            if (null !== $objRules) {
                while ($objRules->next())
                {
                    // Check cart quantity
                    if ($objRules->minItemQuantity > 0 || $objRules->maxItemQuantity > 0)
                    {
                        if ($objRules->quantityMode == 'cart_products') {
                            $intTotal = Isotope::getCart()->countItems();
                        } elseif ($objRules->quantityMode == 'cart_items') {
                            $intTotal = Isotope::getCart()->sumItemsQuantity();
                        } else {
                            $objItem = Isotope::getCart()->getItemForProduct($objSource);
                            $intTotal = (null === $objItem) ? 0 : $objItem->quantity;
                        }

                        if (($objRules->minItemQuantity > 0 && $objRules->minItemQuantity > $intTotal) || ($objRules->maxItemQuantity > 0 && $objRules->maxItemQuantity < $intTotal)) {
                            continue;
                        }
                    }

                    // We're unable to apply variant price rules to low_price (see #3189)
                    if ($strField == 'low_price' && $objRules->productRestrictions == 'variants') {
                        continue;
                    }

                    if ($objRules->current()->isPercentage()) {
                        $fltDiscount = 100 + $objRules->current()->getPercentage();
                        $fltDiscount = round($fltPrice - ($fltPrice / 100 * $fltDiscount), 10);
                        $fltDiscount = $fltDiscount > 0 ? (floor($fltDiscount * 100) / 100) : (ceil($fltDiscount * 100) / 100);

                        $fltPrice = $fltPrice - $fltDiscount;
                    } else {
                        $fltPrice = $fltPrice + $objRules->discount;
                    }
                }
            }
        }

        return $fltPrice;
    }


    /**
     * Add cart rules to surcharges
     */
    public function findSurcharges(IsotopeProductCollection $objCollection)
    {
        // Rules should only be applied to Cart, not any other product collection
        if (!($objCollection instanceof Cart)) {
            return array();
        }

        $arrSurcharges = array();
        $objRules = Rule::findForCart();
        if (null !== $objRules) {
            while ($objRules->next()) {
                $objSurcharge = RuleSurcharge::createForRuleInCollection($objRules->current(), $objCollection);

                if (null !== $objSurcharge) {
                    $arrSurcharges[] = $objSurcharge;
                }
            }
        }

        $arrCoupons = deserialize($objCollection->coupons);

        if (!empty($arrCoupons) && is_array($arrCoupons)) {
            $arrDropped = array();

            foreach ($arrCoupons as $code) {
                $objRule = Rule::findOneByCouponCode($code, $objCollection->getItems());

                if (null === $objRule) {
                    $arrDropped[] = $code;
                } else {
                    // cart rules should total all eligible products for the cart discount and apply the discount to that amount rather than individual products.
                    $objSurcharge = RuleSurcharge::createForRuleInCollection($objRule, $objCollection);

                    if (null !== $objSurcharge) {
                        $arrSurcharges[] = $objSurcharge;
                    }
                }
            }

            if (!empty($arrDropped)) {
                // @todo show dropped coupons
                $arrCoupons = array_diff($arrCoupons, $arrDropped);
                \Database::getInstance()->query("UPDATE tl_iso_cart SET coupons='" . serialize($arrCoupons) . "' WHERE id=".(int) Isotope::getCart()->id);
            }
        }

        return $arrSurcharges;
    }


    /**
     * Returns a rule form if needed
     * @access public
     * @param  object $objModule
     * @return string
     */
    public function getCouponForm($objModule)
    {
        $arrCoupons = deserialize(Isotope::getCart()->coupons);

        if (!is_array($arrCoupons)) {
            $arrCoupons = array();
        }

        $strCoupon = \Input::get('coupon_'.$objModule->id);

        if ($strCoupon == '') {
            $strCoupon = \Input::get('coupon');
        }

        if ($strCoupon != '') {
            $objRule = Rule::findOneByCouponCode($strCoupon, Isotope::getCart()->getItems());

            if (null === $objRule) {
                $_SESSION['COUPON_FAILED'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponInvalid'], $strCoupon);
            }
            else {

                if (in_array(strtolower($strCoupon), array_map('strtolower', $arrCoupons))) {
                    $_SESSION['COUPON_FAILED'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponDuplicate'], $strCoupon);
                } else {
                    $arrCoupons[] = $objRule->code;

                    Isotope::getCart()->coupons = serialize($arrCoupons);
                    Isotope::getCart()->save();

                    $_SESSION['COUPON_SUCCESS'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponApplied'], $objRule->code);
                }
            }

            \Controller::redirect(preg_replace('@[?&]coupon(_[0-9]+)?=[^&]*@', '', \Environment::get('request')));
        }


        $objRules = Rule::findForCartWithCoupons();

        if (null === $objRules || !count(array_diff($objRules->fetchEach('code'), $arrCoupons))) {
            return '';
        }


        //build template
        $objTemplate = new \Isotope\Template('iso_coupons');

        $objTemplate->id = $objModule->id;
        $objTemplate->action = \Environment::get('request');
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['couponHeadline'];
        $objTemplate->inputLabel = $GLOBALS['TL_LANG']['MSC']['couponLabel'];
        $objTemplate->sLabel = $GLOBALS['TL_LANG']['MSC']['couponApply'];

        if ($_SESSION['COUPON_FAILED'][$objModule->id] != '') {
            $objTemplate->message = $_SESSION['COUPON_FAILED'][$objModule->id];
            $objTemplate->mclass = 'failed';
            unset($_SESSION['COUPON_FAILED']);

        } elseif ($_SESSION['COUPON_SUCCESS'][$objModule->id] != '') {
            $objTemplate->message = $_SESSION['COUPON_SUCCESS'][$objModule->id];
            $objTemplate->mclass = 'success';
            unset($_SESSION['COUPON_SUCCESS']);
        }

        return $objTemplate->parse();
    }


    /**
     * Callback for checkout Hook. Transfer active rules to usage table.
     */
    public function writeRuleUsages($objOrder, $objCart)
    {
        $objRules = Rule::findActiveWitoutCoupons();
        $arrRules = (null === $objRules) ? array() : $objRules->fetchEach('id');
        $arrCoupons = deserialize($objCart->coupons);

        if (is_array($arrCoupons) && !empty($arrCoupons)) {
            $blnError = false;

            foreach ($arrCoupons as $k => $code) {
                $objRule = Rule::findOneByCouponCode($code, $objCart->getItems());

                if (null === $objRule) {
                    $_SESSION['ISO_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['couponCodeDropped'], $code);
                    unset($arrCoupons[$k]);
                    $blnError = true;
                } else {
                    $arrRules[] = $objRule->id;
                }
            }

            if ($blnError) {
                $objCart->coupons = $arrCoupons;

                return false;
            }
        }

        if (!empty($arrRules)) {
            $time = time();

            \Database::getInstance()->query("INSERT INTO tl_iso_rule_usage (pid,tstamp,order_id,config_id,member_id) VALUES (" . implode(", $time, {$objOrder->id}, ".(int) Isotope::getConfig()->id.", {$objOrder->member}), (", $arrRules) . ", $time, {$objOrder->id}, ".(int) Isotope::getConfig()->id.", {$objOrder->member})");
        }

        return true;
    }

    /**
     * Callback for checkout step "review". Remove rule usages if an order failed.
     * @todo this will no longer work
     */
    public function cleanRuleUsages(&$objModule)
    {
        \Database::getInstance()->query("DELETE FROM tl_iso_rule_usage WHERE pid=(SELECT id FROM tl_iso_product_collection WHERE type='order' AND source_collection_id=".(int) Isotope::getCart()->id.")");

        return '';
    }


    /**
     * Transfer coupons from one cart to another. This happens if a guest cart is moved to user cart.
     * @param IsotopeProductCollection
     * @param IsotopeProductCollection
     * @param array
     */
    public function transferCoupons($objOldCollection, $objNewCollection, $arrIds)
    {
        if ($objOldCollection instanceof Cart && $objNewCollection instanceof Cart) {
            $objNewCollection->coupons = $objOldCollection->coupons;
        }
    }
}
