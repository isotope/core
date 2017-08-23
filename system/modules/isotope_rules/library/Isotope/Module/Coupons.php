<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\Rule;

class Coupons extends Module
{
    /**
     * Module template
     * @var string
     */
    protected $strTemplate = 'mod_iso_coupons';

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if ('FE' === TL_MODE && null === Rule::findForCartWithCoupons()) {
            return '';
        }

        return parent::generate();
    }

    /**
     * {@inheritdoc}
     */
    protected function compile()
    {
        $cart = Isotope::getCart();
        $coupons = deserialize($cart->coupons);

        if (!is_array($coupons)) {
            $coupons = array();
        }

        if ('add_coupon_'.$this->id === \Input::post('FORM_SUBMIT')) {
            $this->addCoupon(\Input::post('coupon'), $coupons, $cart);
        } elseif ('remove_coupon_'.$this->id === \Input::post('FORM_SUBMIT')) {
            $this->removeCoupon(\Input::post('coupon'), $coupons, $cart);
        }

        $this->Template->action = \Environment::get('request');
        $this->Template->coupons = $coupons;
        $this->Template->inputLabel = $GLOBALS['TL_LANG']['MSC']['couponLabel'];
        $this->Template->sLabel = $GLOBALS['TL_LANG']['MSC']['couponApply'];
    }

    private function addCoupon($coupon, array &$coupons, Cart $cart)
    {
        $rule = Rule::findOneByCouponCode($coupon, $cart->getItems());

        if (null === $rule) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['couponInvalid'], $coupon));
        } elseif (in_array(strtolower($coupon), array_map('strtolower', $coupons), true)) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['couponDuplicate'], $coupon));
        } else {
            $coupons[] = $rule->code;

            $cart->coupons = serialize($coupons);
            $cart->save();

            Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['MSC']['couponApplied'], $rule->code));
        }

        \Controller::reload();
    }

    private function removeCoupon($coupon, array $coupons, Cart $cart)
    {
        $pos = array_search($coupon, $coupons);

        if (false !== $pos) {
            unset($coupons[$pos]);
            $cart->coupons = serialize($coupons);
            $cart->save();
        }

        \Controller::reload();
    }
}
