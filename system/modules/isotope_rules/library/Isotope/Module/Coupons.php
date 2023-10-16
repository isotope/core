<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use Contao\StringUtil;
use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\Rule;

class Coupons extends Module
{
    /**
     * Module template
     * @var string
     */
    protected $strTemplate = 'mod_iso_coupons';

    /**
     * @var \Isotope\Model\ProductCollection\Cart
     */
    private $cart;

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $this->cart = Isotope::getCart();

        if ('FE' === TL_MODE && ($this->cart->isEmpty() || null === Rule::findForCartWithCoupons())) {
            return '';
        }

        return parent::generate();
    }

    /**
     * {@inheritdoc}
     */
    protected function compile()
    {
        $coupons = StringUtil::deserialize($this->cart->coupons);

        if (!\is_array($coupons)) {
            $coupons = array();
        }

        if ('add_coupon_'.$this->id === Input::post('FORM_SUBMIT')) {
            $this->addCoupon(Input::post('coupon'), $coupons);
        } elseif ('remove_coupon_'.$this->id === Input::post('FORM_SUBMIT')) {
            $this->removeCoupon(Input::post('coupon'), $coupons);
        }

        $this->Template->action = Environment::get('request');
        $this->Template->coupons = $coupons;
        $this->Template->inputLabel = $GLOBALS['TL_LANG']['MSC']['couponLabel'];
        $this->Template->sLabel = $GLOBALS['TL_LANG']['MSC']['couponApply'];
    }

    private function addCoupon($coupon, array &$coupons)
    {
        $rule = Rule::findOneByCouponCode($coupon, $this->cart->getItems());

        if (null === $rule) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['couponInvalid'], $coupon));
        } elseif (\in_array(mb_strtolower($coupon), array_map('mb_strtolower', $coupons), true)) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['couponDuplicate'], $coupon));
        } elseif ($rule->singleCode && !empty($coupons)) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['couponSingle'], $coupon));
        } else {
            $coupons[] = $rule->code;

            $this->cart->coupons = serialize($coupons);
            $this->cart->save();

            Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['MSC']['couponApplied'], $rule->code));
        }

        Controller::reload();
    }

    private function removeCoupon($coupon, array $coupons)
    {
        $pos = array_search($coupon, $coupons);

        if (false !== $pos) {
            unset($coupons[$pos]);
            $this->cart->coupons = serialize($coupons);
            $this->cart->save();
        }

        Controller::reload();
    }
}
