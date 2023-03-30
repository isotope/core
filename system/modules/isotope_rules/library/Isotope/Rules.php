<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\ModuleModel;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopePrice;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionSurcharge\Rule as RuleSurcharge;
use Isotope\Model\Rule;

class Rules extends Controller
{

    /**
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstance;

    /**
     * Prevent cloning of the object (Singleton)
     */
    private function __clone() {}


    /**
     * Prevent direct instantiation (Singleton)
     */
    protected function __construct()
    {
        parent::__construct();

        // User object must be loaded from cart, e.g. for postsale handling
        if (Isotope::getCart()->member > 0) {
            $this->User = Database::getInstance()->prepare('SELECT * FROM tl_member WHERE id=?')->execute(Isotope::getCart()->member);
        }
    }


    /**
     * Instantiate the singleton if necessary and return it
     * @return object
     */
    public static function getInstance()
    {
        if (!\is_object(static::$objInstance)) {
            static::$objInstance = new Rules();
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
        if ($objSource instanceof IsotopePrice && ('price' === $strField || 'low_price' === $strField || 'net_price' === $strField || 'gross_price' === $strField)) {

        // @todo try not to use getRelated() because it loads variants
        $objRules = Rule::findByProduct($objSource->getRelated('pid'), $strField, $fltPrice);

        if (null !== $objRules) {
                while ($objRules->next()) {
                    // Check cart quantity
                    if ($objRules->minItemQuantity > 0 || $objRules->maxItemQuantity > 0) {
                        if ('cart_products' === $objRules->quantityMode) {
                            $intTotal = Isotope::getCart()->countItems();
                        } elseif ('cart_items' === $objRules->quantityMode) {
                            $intTotal = Isotope::getCart()->sumItemsQuantity();
                        } else {
                            $objItem = Isotope::getCart()->getItemForProduct($objSource->getRelated('pid'));
                            $intTotal = (null === $objItem) ? 0 : $objItem->quantity;
                        }

                        if (($objRules->minItemQuantity > 0 && $objRules->minItemQuantity > $intTotal) || ($objRules->maxItemQuantity > 0 && $objRules->maxItemQuantity < $intTotal)) {
                    continue;
                        }
                    }

                    // We're unable to apply variant price rules to low_price (see #3189)
                    if ('low_price' === $strField && 'variants' === $objRules->productRestrictions) {
                        continue;
                    }

                    if ($objRules->current()->isPercentage()) {
                        $fltDiscount = 100 + $objRules->current()->getPercentage();
                        $fltDiscount = round($fltPrice - ($fltPrice / 100 * $fltDiscount), 10);

                        $precision = Isotope::getConfig()->priceRoundPrecision;
                        $factor    = pow(10, 2);
                        $up        = $fltDiscount > 0 ? 'ceil' : 'floor';
                        $down      = $fltDiscount > 0 ? 'floor' : 'ceil';

                        switch ($objRules->rounding) {
                            case Rule::ROUND_NORMAL:
                                $fltDiscount = round($fltDiscount, $precision);
                                break;

                            case Rule::ROUND_UP:
                                $fltDiscount = $up($fltDiscount * $factor) / $factor;
                                break;

                            case Rule::ROUND_DOWN:
                            default:
                                $fltDiscount = $down($fltDiscount * $factor) / $factor;
                                break;
                        }

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
        $objCart = $objCollection;

        // The checkout review pages shows an order, but we need the cart
        // Only the cart contains coupons etc.
        if ($objCollection instanceof Order) {
            $objCart = $objCollection->getRelated('source_collection_id');
        }

        // Rules should only be applied to Cart, not any other product collection
        if (!($objCart instanceof Cart)) {
            return array();
        }

        $arrSurcharges = array();
        $objRules = Rule::findForCart();
        if (null !== $objRules) {
            foreach ($objRules as $objRule) {
                $this->addSurchargesForRule($objRule, $objCollection, $arrSurcharges);
            }
        }

        $arrCoupons = StringUtil::deserialize($objCart->coupons);

        if (!empty($arrCoupons) && \is_array($arrCoupons)) {
            $blnHasCode = false;
            $arrDropped = array();

            foreach ($arrCoupons as $code) {
                $objRule = Rule::findOneByCouponCode($code, $objCollection->getItems());

                if (null === $objRule || ($blnHasCode && $objRule->singleCode)) {
                    $arrDropped[] = $code;
                } else {
                    $blnHasCode = $this->addSurchargesForRule($objRule, $objCollection, $arrSurcharges) ?: $blnHasCode;
                }
            }

            if (!empty($arrDropped)) {
                // @todo show dropped coupons
                $arrCoupons = array_diff($arrCoupons, $arrDropped);
                Database::getInstance()->query("UPDATE tl_iso_product_collection SET coupons='" . serialize($arrCoupons) . "' WHERE id=" . (int) Isotope::getCart()->id);
            }
        }

        return $arrSurcharges;
    }


    /**
     * Returns a rule form if needed
     *
     * @param ModuleModel $objModule
     *
     * @return string
     *
     * @deprecated Deprecated since Isotope 2.5, use the Coupons front end module instead.
     */
    public function getCouponForm($objModule)
    {
        $arrCoupons = StringUtil::deserialize(Isotope::getCart()->coupons);

        if (!\is_array($arrCoupons)) {
            $arrCoupons = array();
        }

        $strCoupon = Input::get('coupon_' . $objModule->id);

        if ($strCoupon == '') {
            $strCoupon = Input::get('coupon');
        }

        if ($strCoupon != '') {
            $objRule = Rule::findOneByCouponCode($strCoupon, Isotope::getCart()->getItems());

            if (null === $objRule) {
                $_SESSION['COUPON_FAILED'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponInvalid'], $strCoupon);
            } elseif (\in_array(mb_strtolower($strCoupon), array_map('mb_strtolower', $arrCoupons), true)) {
                $_SESSION['COUPON_FAILED'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponDuplicate'], $strCoupon);
            } elseif ($objRule->singleCode && !empty($arrCoupons)) {
                $_SESSION['COUPON_FAILED'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponSingle'], $strCoupon);
            } else {
                $arrCoupons[] = $objRule->code;

                Isotope::getCart()->coupons = serialize($arrCoupons);
                Isotope::getCart()->save();

                $_SESSION['COUPON_SUCCESS'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponApplied'], $objRule->code);
            }

            Controller::redirect(preg_replace('@[?&]coupon(_[0-9]+)?=[^&]*@', '', Environment::get('request')));
        }


        $objRules = Rule::findForCartWithCoupons();

        if (null === $objRules || ModuleModel::countBy('type', 'iso_coupons') > 0) {
            return '';
        }


        //build template
        $objTemplate = new Template('iso_coupons');

        $objTemplate->id = $objModule->id;
        $objTemplate->action = Environment::get('request');
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['couponHeadline'];
        $objTemplate->inputLabel = $GLOBALS['TL_LANG']['MSC']['couponLabel'];
        $objTemplate->sLabel = $GLOBALS['TL_LANG']['MSC']['couponApply'];
        $objTemplate->usedCoupons = $arrCoupons;
        $objTemplate->rules = $objRules;

        if (!empty($_SESSION['COUPON_FAILED'][$objModule->id])) {
            $objTemplate->message = $_SESSION['COUPON_FAILED'][$objModule->id];
            $objTemplate->mclass = 'failed';
            unset($_SESSION['COUPON_FAILED']);

        } elseif (!empty($_SESSION['COUPON_SUCCESS'][$objModule->id])) {
            $objTemplate->message = $_SESSION['COUPON_SUCCESS'][$objModule->id];
            $objTemplate->mclass = 'success';
            unset($_SESSION['COUPON_SUCCESS']);
        }

        return $objTemplate->parse();
    }


    /**
     * Callback for checkout Hook. Transfer active rules to usage table.
     */
    public function writeRuleUsages($objOrder)
    {
        $objCart = Cart::findByPk($objOrder->source_collection_id);

        $objRules = Rule::findActiveWithoutCoupons();
        $arrRules = (null === $objRules) ? array() : $objRules->fetchEach('id');
        $arrCoupons = StringUtil::deserialize($objCart->coupons);

        if (\is_array($arrCoupons) && !empty($arrCoupons)) {
            $blnError = false;

            foreach ($arrCoupons as $k => $code) {
                $objRule = Rule::findOneByCouponCode($code, $objCart->getItems());

                if (null === $objRule) {
                    Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['couponCodeDropped'], $code));
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

            Database::getInstance()->query("INSERT INTO tl_iso_rule_usage (pid,tstamp,order_id,config_id,member_id) VALUES (" . implode(", $time, {$objOrder->id}, " . (int) Isotope::getConfig()->id . ", {$objOrder->member}), (", $arrRules) . ", $time, {$objOrder->id}, " . (int) Isotope::getConfig()->id . ", {$objOrder->member})");
        }

        return true;
    }

    /**
     * Callback for checkout step "review". Remove rule usages if an order failed.
     * @todo this will no longer work
     */
    public function cleanRuleUsages(&$objModule)
    {
        Database::getInstance()->query("DELETE FROM tl_iso_rule_usage WHERE pid=(SELECT id FROM tl_iso_product_collection WHERE type='order' AND source_collection_id=" . (int) Isotope::getCart()->id . ")");

        return '';
    }


    /**
     * Transfer coupons from one cart to another. This happens if a guest cart is moved to user cart.
     *
     * @param IsotopeProductCollection $oldCollection
     * @param IsotopeProductCollection $newCollection
     */
    public function transferCoupons(IsotopeProductCollection $oldCollection, IsotopeProductCollection $newCollection)
    {
        if ($oldCollection instanceof Cart && $newCollection instanceof Cart) {
            $oldCoupons = StringUtil::deserialize($oldCollection->coupons, true);
            $newCoupons = StringUtil::deserialize($newCollection->coupons, true);

            $newCollection->coupons = array_unique(array_merge($oldCoupons, $newCoupons));
            $newCollection->save();
        }
    }

    /**
     * Delete rule usages after an order has been deleted
     *
     * @param IsotopeProductCollection $objCollection
     * @param int                      $intId
     */
    public function deleteRuleUsages($objCollection, $intId)
    {
        Database::getInstance()->prepare("DELETE FROM tl_iso_rule_usage WHERE order_id=?")->execute($intId);
    }

    private function addSurchargesForRule(Rule $objRule, IsotopeProductCollection $objCollection, array &$arrSurcharges)
    {
        switch ($objRule->type) {
            case 'cart':
                $objSurcharge = RuleSurcharge::createForRuleInCollection($objRule, $objCollection);

                if (null === $objSurcharge) {
                    return false;
                }

                $arrSurcharges[] = $objSurcharge;
                return true;

            case 'cart_group':
                $blnResult = false;
                $ids = StringUtil::deserialize($objRule->groupRules);

                if (!empty($ids) && \is_array($ids)) {
                    foreach ($ids as $id) {
                        $objRules = Rule::findForCart($id);

                        if (null === $objRules) {
                            continue;
                        }

                        $blnResult = $this->addSurchargesForRule($objRules->current(), $objCollection, $arrSurcharges) ?: $blnResult;

                        if ($blnResult && $objRule->groupCondition === Rule::GROUP_FIRST) {
                            break;
                        }
                    }
                }

                return $blnResult;

            default:
                throw new \RuntimeException('Unsupported rule type "'.$objRule->type.'" for cart group');
        }
    }
}
