<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CheckoutStep;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Shipping;


class ShippingMethod extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true if the current cart has shipping
     * @return  bool
     */
    public function isAvailable()
    {
        return Isotope::getCart()->requiresShipping();
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $arrModules = array();
        $arrOptions = array();

        $arrIds = deserialize($this->objModule->iso_shipping_modules);

        if (!empty($arrIds) && is_array($arrIds)) {
            $objModules = Shipping::findBy(array('id IN (' . implode(',', $arrIds) . ')', (BE_USER_LOGGED_IN === true ? '' : "enabled='1'")), null, array('order' => \Database::getInstance()->findInSet('id', $arrIds)));

            if (null !== $objModules) {
                while ($objModules->next()) {

                    $objModule = $objModules->current();

                    if (!$objModule->isAvailable()) {
                        continue;
                    }

                    $strLabel = $objModule->getLabel();
                    $fltPrice = $objModule->getPrice();

                    if ($fltPrice > 0) {
                        if ($objModule->isPercentage()) {
                            $strLabel .= ' (' . $objModule->getPercentageLabel() . ')';
                        }

                        $strLabel .= ': ' . Isotope::formatPriceWithCurrency($fltPrice);
                    }

                    if ($objModule->note != '') {
                        $strLabel .= '<span class="note">' . $objModule->note . '</span>';
                    }

                    $arrOptions[] = array(
                        'value' => $objModule->id,
                        'label' => $strLabel,
                    );

                    $arrModules[$objModule->id] = $objModule;
                }
            }
        }

        if (empty($arrModules)) {
            $this->blnError = true;

            \System::log('No shipping methods available for cart ID ' . Isotope::getCart()->id, __METHOD__, TL_ERROR);

            $objTemplate           = new \Isotope\Template('mod_message');
            $objTemplate->class    = 'shipping_method';
            $objTemplate->hl       = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['shipping_method'];
            $objTemplate->type     = 'error';
            $objTemplate->message  = $GLOBALS['TL_LANG']['MSC']['noShippingModules'];

            return $objTemplate->parse();
        }

        $strClass  = $GLOBALS['TL_FFL']['radio'];
        $objWidget = new $strClass(array(
                                        'id'          => $this->getStepClass(),
                                        'name'        => $this->getStepClass(),
                                        'mandatory'   => true,
                                        'options'     => $arrOptions,
                                        'value'       => Isotope::getCart()->shipping_id,
                                        'storeValues' => true,
                                        'tableless'   => true,
                                   ));

        // If there is only one shipping method, mark it as selected by default
        if (count($arrModules) == 1) {
            $objModule        = reset($arrModules);
            $objWidget->value = $objModule->id;
            Isotope::getCart()->setShippingMethod($objModule);
        }

        if (\Input::post('FORM_SUBMIT') == $this->objModule->getFormId()) {
            $objWidget->validate();

            if (!$objWidget->hasErrors()) {
                Isotope::getCart()->setShippingMethod($arrModules[$objWidget->value]);
            }
        }

        $objTemplate = new \Isotope\Template('iso_checkout_shipping_method');

        if (!Isotope::getCart()->hasShipping() || !isset($arrModules[Isotope::getCart()->shipping_id])) {
            $this->blnError = true;
        }

        $objTemplate->headline        = $GLOBALS['TL_LANG']['MSC']['shipping_method'];
        $objTemplate->message         = $GLOBALS['TL_LANG']['MSC']['shipping_method_message'];
        $objTemplate->options         = $objWidget->parse();
        $objTemplate->shippingMethods = $arrModules;

        return $objTemplate->parse();
    }

    /**
     * Return review information for last page of checkout
     * @return  string
     */
    public function review()
    {
        return array(
            'shipping_method' => array(
                'headline' => $GLOBALS['TL_LANG']['MSC']['shipping_method'],
                'info'     => Isotope::getCart()->getShippingMethod()->checkoutReview(),
                'note'     => Isotope::getCart()->getShippingMethod()->note,
                'edit'     => \Isotope\Module\Checkout::generateUrlForStep('shipping'),
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
