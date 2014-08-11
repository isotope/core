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

namespace Isotope\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Payment;


class PaymentMethod extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true if the current cart has payment
     * @return  bool
     */
    public function isAvailable()
    {
        return Isotope::getCart()->requiresPayment();
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $arrModules = array();
        $arrOptions = array();

        $arrIds = deserialize($this->objModule->iso_payment_modules);

        if (!empty($arrIds) && is_array($arrIds)) {
            $arrColumns = array('id IN (' . implode(',', $arrIds) . ')');

            if (BE_USER_LOGGED_IN !== true) {
                $arrColumns[] = "enabled='1'";
            }

            /** @type Payment[] $objModules */
            $objModules = Payment::findBy($arrColumns, null, array('order' => \Database::getInstance()->findInSet('id', $arrIds)));

            if (null !== $objModules) {
                foreach ($objModules as $objModule) {

                    if (!$objModule->isAvailable()) {
                        continue;
                    }

                    $strLabel = $objModule->getLabel();
                    $fltPrice = $objModule->getPrice();

                    if ($fltPrice != 0) {
                        if ($objModule->isPercentage()) {
                            $strLabel .= ' (' . $objModule->getPercentageLabel() . ')';
                        }

                        $strLabel .= ': ' . Isotope::formatPriceWithCurrency($fltPrice);
                    }

                    if ($objModule->note != '') {
                        $strLabel .= '<span class="note">' . $objModule->note . '</span>';
                    }

                    $arrOptions[] = array(
                        'value'     => $objModule->id,
                        'label'     => $strLabel,
                    );

                    $arrModules[$objModule->id] = $objModule;
                }
            }
        }

        if (empty($arrModules)) {
            $this->blnError = true;

            \System::log('No payment methods available for cart ID ' . Isotope::getCart()->id, __METHOD__, TL_ERROR);

            $objTemplate           = new \Isotope\Template('mod_message');
            $objTemplate->class    = 'payment_method';
            $objTemplate->hl       = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['payment_method'];
            $objTemplate->type     = 'error';
            $objTemplate->message  = $GLOBALS['TL_LANG']['MSC']['noPaymentModules'];

            return $objTemplate->parse();
        }

        $strClass  = $GLOBALS['TL_FFL']['radio'];

        /** @type \Widget $objWidget */
        $objWidget = new $strClass(array(
            'id'            => $this->getStepClass(),
            'name'          => $this->getStepClass(),
            'mandatory'     => true,
            'options'       => $arrOptions,
            'value'         => Isotope::getCart()->payment_id,
            'storeValues'   => true,
            'tableless'     => true,
        ));

        // If there is only one payment method, mark it as selected by default
        if (count($arrModules) == 1) {
            $objModule        = reset($arrModules);
            $objWidget->value = $objModule->id;
            Isotope::getCart()->setPaymentMethod($objModule);
        }

        if (\Input::post('FORM_SUBMIT') == $this->objModule->getFormId()) {
            $objWidget->validate();

            if (!$objWidget->hasErrors()) {
                Isotope::getCart()->setPaymentMethod($arrModules[$objWidget->value]);
            }
        }

        $objTemplate = new \Isotope\Template('iso_checkout_payment_method');

        if (!Isotope::getCart()->hasPayment() || !isset($arrModules[Isotope::getCart()->payment_id])) {
            $this->blnError = true;
        }

        $objTemplate->headline       = $GLOBALS['TL_LANG']['MSC']['payment_method'];
        $objTemplate->message        = $GLOBALS['TL_LANG']['MSC']['payment_method_message'];
        $objTemplate->options        = $objWidget->parse();
        $objTemplate->paymentMethods = $arrModules;

        return $objTemplate->parse();
    }

    /**
     * Return review information for last page of checkout
     * @return  string
     */
    public function review()
    {
        return array(
            'payment_method' => array(
                'headline' => $GLOBALS['TL_LANG']['MSC']['payment_method'],
                'info'     => Isotope::getCart()->getPaymentMethod()->checkoutReview(),
                'note'     => Isotope::getCart()->getPaymentMethod()->note,
                'edit'     => \Isotope\Module\Checkout::generateUrlForStep('payment'),
            ),
        );
    }

    /**
     * Return array of tokens for notification
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        return array();
    }
}
