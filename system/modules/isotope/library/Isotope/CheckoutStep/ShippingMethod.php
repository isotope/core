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
            $objModules = Shipping::findBy(array('id IN (' . implode(',', $arrIds) . ')', (BE_USER_LOGGED_IN === true ? '' : "enabled='1'")), null, array('order'=>\Database::getInstance()->findInSet('id', $arrIds)));

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

            \System::log('No shipping methods available for cart ID ' . Isotope::getCart()->id, __METHOD__, TL_ERROR);

            $objTemplate = new \Isotope\Template('mod_message');
            $objTemplate->class = 'shipping_method';
            $objTemplate->hl = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['shipping_method'];
            $objTemplate->type = 'error';
            $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noShippingModules'];

            return $objTemplate->parse();
        }

        $strClass = $GLOBALS['TL_FFL']['radio'];
        $objWidget = new $strClass(array(
            'id'            => $this->getStepClass(),
            'name'          => $this->getStepClass(),
            'mandatory'     => true,
            'options'       => $arrOptions,
            'value'         => Isotope::getCart()->shipping_id,
            'storeValues'   => true,
            'tableless'     => true,
        ));

        // If there is only one shipping method, mark it as selected by default
        if (count($arrModules) == 1) {
            $objModule = reset($arrModules);
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

        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['shipping_method'];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['shipping_method_message'];
        $objTemplate->options = $objWidget->parse();
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
                'headline'    => $GLOBALS['TL_LANG']['MSC']['shipping_method'],
                'info'        => Isotope::getCart()->getShippingMethod()->checkoutReview(),
                'note'        => Isotope::getCart()->getShippingMethod()->note,
                'edit'        => \Isotope\Module\Checkout::generateUrlForStep('shipping'),
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
        $objShipping = $objCollection->getShippingMethod();

        return array(
            'shipping_id'        => $objShipping->id,
            'shipping_label'     => $objShipping->getLabel(),
            'shipping_note'      => $objShipping->note,
            'shipping_note_text' => strip_tags($objShipping->note),
        );
    }
}
