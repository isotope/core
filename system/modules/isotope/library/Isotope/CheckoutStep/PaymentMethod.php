<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CheckoutStep;

use Contao\Database;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Isotope;
use Isotope\Model\Payment;
use Isotope\Module\Checkout;
use Isotope\Template;

/**
 * PaymentMethod checkout step lets the user choose a payment method
 */
class PaymentMethod extends CheckoutStep implements IsotopeCheckoutStep
{
    /**
     * Payment modules
     * @var array
     */
    private $modules;

    /**
     * Payment options
     * @var array
     */
    private $options;

    /**
     * Returns true if the current cart has payment
     *
     * @inheritdoc
     */
    public function isAvailable()
    {
        $available = Isotope::getCart()->requiresPayment();

        if (!$available) {
            Isotope::getCart()->setPaymentMethod(null);
        }

        return $available;
    }

    /**
     * @inheritdoc
     */
    public function isSkippable()
    {
        if (!$this->objModule->canSkipStep('payment_method')) {
            return false;
        }

        $this->initializeModules();

        return 1 === \count($this->options);
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $this->initializeModules();

        if (empty($this->modules)) {
            $this->blnError = true;

            System::log('No payment methods available for cart ID ' . Isotope::getCart()->id, __METHOD__, TL_ERROR);

            /** @var Template|\stdClass $objTemplate */
            $objTemplate           = new Template('mod_message');
            $objTemplate->class    = 'payment_method';
            $objTemplate->hl       = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['payment_method'];
            $objTemplate->type     = 'error';
            $objTemplate->message  = $GLOBALS['TL_LANG']['MSC']['noPaymentModules'];

            return $objTemplate->parse();
        }

        $strClass  = $GLOBALS['TL_FFL']['radio'];

        /** @var Widget $objWidget */
        $objWidget = new $strClass(array(
            'id'            => $this->getStepClass(),
            'name'          => $this->getStepClass(),
            'mandatory'     => true,
            'options'       => $this->options,
            'value'         => Isotope::getCart()->payment_id,
            'storeValues'   => true,
            'tableless'     => true,
        ));

        // If there is only one payment method, mark it as selected by default
        if (\count($this->modules) == 1) {
            $objModule        = reset($this->modules);
            $objWidget->value = $objModule->id;
            Isotope::getCart()->setPaymentMethod($objModule);
        }

        if (Input::post('FORM_SUBMIT') == $this->objModule->getFormId()) {
            $objWidget->validate();

            if (!$objWidget->hasErrors()) {
                Isotope::getCart()->setPaymentMethod($this->modules[$objWidget->value]);
            }
        }

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_checkout_payment_method');

        if (!Isotope::getCart()->hasPayment() || !isset($this->modules[Isotope::getCart()->payment_id])) {
            $this->blnError = true;
        }

        $objTemplate->headline       = $GLOBALS['TL_LANG']['MSC']['payment_method'];
        $objTemplate->message        = $GLOBALS['TL_LANG']['MSC']['payment_method_message'];
        $objTemplate->options        = $objWidget->parse();
        $objTemplate->paymentMethods = $this->modules;

        return $objTemplate->parse();
    }

    /**
     * Return review information for last page of checkout
     * @return  array
     */
    public function review()
    {
        return array(
            'payment_method' => array(
                'headline' => $GLOBALS['TL_LANG']['MSC']['payment_method'],
                'info'     => Isotope::getCart()->getDraftOrder()->getPaymentMethod()->checkoutReview(),
                'note'     => Isotope::getCart()->getDraftOrder()->getPaymentMethod()->getNote(),
                'edit'     => $this->isSkippable() ? '' : Checkout::generateUrlForStep(Checkout::STEP_PAYMENT),
            ),
        );
    }

    /**
     * Initialize modules and options
     */
    private function initializeModules()
    {
        if (null !== $this->modules && null !== $this->options) {
            return;
        }

        $this->modules = array();
        $this->options = array();

        $arrIds = StringUtil::deserialize($this->objModule->iso_payment_modules);

        if (!empty($arrIds) && \is_array($arrIds)) {
            $arrColumns = array('id IN (' . implode(',', $arrIds) . ')');

            if (!\Contao\System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
                $arrColumns[] = "enabled='1'";
            }

            /** @var Payment[] $objModules */
            $objModules = Payment::findBy($arrColumns, null, array('order' => Database::getInstance()->findInSet('id', $arrIds)));

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

                    if ($note = $objModule->getNote()) {
                        $strLabel .= '<span class="note">' . $note . '</span>';
                    }

                    $this->options[] = array(
                        'value'     => $objModule->id,
                        'label'     => $strLabel,
                    );

                    $this->modules[$objModule->id] = $objModule;
                }
            }
        }
    }
}
