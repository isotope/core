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

namespace Isotope\Model\Payment;

use Haste\Form\Form;
use Isotope\Isotope;


class BillpayWithSaferpay extends Saferpay
{

    public function isAvailable()
    {
        $objAddress = Isotope::getCart()->getBillingAddress();

        if (null === $objAddress || !in_array($objAddress->country, array('de', 'ch', 'at'))) {
            return false;
        }

        return parent::isAvailable();
    }

    public static function addOrderCondition(Form $objForm, \Module $objModule)
    {
        $objPayment = Isotope::getCart()->getPaymentMethod();

        if (null !== $objPayment && $objPayment instanceof BillpayWithSaferpay) {

            $strLabel = $GLOBALS['TL_LANG']['MSC']['billpay_agb_'.Isotope::getCart()->getBillingAddress()->country];

            if ($strLabel == '') {
                throw new \LogicException('Missing BillPay AGB for country "' . Isotope::getCart()->getBillingAddress()->country . '" and language "' . $GLOBALS['TL_LANGUAGE'] . '"');
            }

            $objForm->addFormField(
                'billpay_confirmation',
                array(
                    'label' => array('', $strLabel),
                    'inputType' => 'checkbox',
                    'eval' => array('mandatory'=>true)
                )
            );
        }
    }
}
