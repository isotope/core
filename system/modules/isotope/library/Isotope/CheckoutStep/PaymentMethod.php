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

namespace Isotope\CheckoutStep;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
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
            $objModules = Payment::findBy(array('id IN (' . implode(',', $arrIds) . ')', (BE_USER_LOGGED_IN === true ? '' : "enabled='1'")), null, array('order'=>\Database::getInstance()->findInSet('id', $arrIds)));

            if (null !== $objModules) {
                while ($objModules->next()) {

                    $objModule = $objModules->current();

                    if (!$objModule->isAvailable()) {
                        continue;
                    }

                    $fltPrice = $objModule->price;
                    $strSurcharge = $objModule->surcharge;
                    $strPrice = $fltPrice != 0 ? (($strSurcharge == '' ? '' : ' ('.$strSurcharge.')') . ': '.Isotope::formatPriceWithCurrency($fltPrice)) : '';
                    $strNote = $objModule->note ? '<span class="note">' . $objModule->note . '</span>' : '';

                    $arrOptions[] = array(
                        'value'     => $objModule->id,
                        'label'     => $objModule->getLabel() . $strPrice . $strNote,
                    );

                    $arrModules[$objModule->id] = $objModule;
                }
            }
        }

        if (empty($arrModules)) {
            $this->blnError = true;

            \System::log('No payment methods available for cart ID ' . Isotope::getCart()->id, __METHOD__, TL_ERROR);

            $objTemplate = new \Isotope\Template('mod_message');
            $objTemplate->class = 'payment_method';
            $objTemplate->hl = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['payment_method'];
            $objTemplate->type = 'error';
            $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noPaymentModules'];

            return $objTemplate->parse();
        }

        $strClass = $GLOBALS['TL_FFL']['radio'];
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
            $objModule = reset($arrModules);
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

        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['payment_method'];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_method_message'];
        $objTemplate->options = $objWidget->parse();
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
                'headline'    => $GLOBALS['TL_LANG']['MSC']['payment_method'],
                'info'        => Isotope::getCart()->getPaymentMethod()->checkoutReview(),
                'note'        => Isotope::getCart()->getPaymentMethod()->note,
                'edit'        => \Isotope\Module\Checkout::generateUrlForStep('payment'),
            ),
        );
    }

    /**
     * Return array of tokens for email templates
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getEmailTokens(IsotopeProductCollection $objCollection)
    {
        $objPayment = $objCollection->getPaymentMethod();

        return array(
            'payment_id'        => $objPayment->id,
            'payment_label'     => $objPayment->getLabel(),
            'payment_note'      => $objPayment->note,
            'payment_note_text' => strip_tags($objPayment->note),
        );
    }
}
