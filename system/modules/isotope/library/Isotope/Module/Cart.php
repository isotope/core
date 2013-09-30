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

namespace Isotope\Module;

use Isotope\Isotope;


/**
 * Class Cart
 *
 * Front end module Isotope "cart".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Cart extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_cart';

    /**
     * Disable caching of the frontend page if this module is in use.
     * @var boolean
     */
    protected $blnDisableCache = true;

    /**
     * FORM_SUBMIT value for this module
     * @var string
     */
    protected $strFormId = 'iso_cart_update_';


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: CART ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Add current module ID to FORM_SUBMIT
        $this->strFormId .= $this->id;

        return parent::generate();
    }


    /**
     * Generate module
     */
    protected function compile()
    {
        if (Isotope::getCart()->isEmpty())
        {
            $this->Template->empty = true;
            $this->Template->type = 'empty';
            $this->Template->message = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];

            return;
        }

        // Remove from cart
        if (\Input::get('remove') > 0 && Isotope::getCart()->deleteItemById((int) \Input::get('remove')))
        {
            global $objPage;

            \Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));
        }

        $objTemplate = new \Isotope\Template($this->iso_collectionTpl);

        Isotope::getCart()->addToTemplate(
            $objTemplate,
            array(
                'gallery'   => $this->iso_gallery,
                'sorting'   => $this->getProductCollectionItemsSortingCallable(),
            )
        );

        $blnReload = false;
        $arrQuantity = \Input::post('quantity');
        $arrItems = $objTemplate->items;

        foreach ($arrItems as $k => $arrItem) {

            // Update cart data if form has been submitted
            if (\Input::post('FORM_SUBMIT') == $this->strFormId && is_array($arrQuantity) && isset($arrQuantity[$arrItem['id']]))
            {
                $blnReload = true;
                Isotope::getCart()->updateItemById($arrItem['id'], array('quantity'=>$arrQuantity[$arrItem['id']]));
                continue; // no need to generate $arrProductData, we reload anyway
            }


            $arrItem['remove_href'] = \Isotope\Frontend::addQueryStringToUrl('remove='.$arrItem['id']);
            $arrItem['remove_title'] = specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $arrItem['name']));
            $arrItem['remove_link'] = $GLOBALS['TL_LANG']['MSC']['removeProductLinkText'];

            $arrItems[$k] = $arrItem;
        }

        $arrButtons = $this->generateButtons();

        // Reload the page if no button has handled it
        if ($blnReload)
        {
            \Controller::reload();
        }

        $objTemplate->items = $arrItems;
        $objTemplate->editable = true;
        $objTemplate->linkProducts = true;

        $objTemplate->formId = $this->strFormId;
        $objTemplate->formSubmit = $this->strFormId;
        $objTemplate->action = \Environment::get('request');
        $objTemplate->buttons = $arrButtons;

        $this->Template->empty = false;
        $this->Template->collection = Isotope::getCart();
        $this->Template->products = $objTemplate->parse();
    }

    /**
     * Generate buttons for cart template
     * @return  array
     */
    protected function generateButtons()
    {
        $arrButtons = array();

        // Add "update cart" button
        $arrButtons['update'] = array(
            'type'      => 'submit',
            'name'      => 'button_update',
            'label'     => $GLOBALS['TL_LANG']['MSC']['updateCartBT'],
        );

        // Add button to cart button (usually if not on the cart page)
        if ($this->iso_cart_jumpTo > 0) {
            $objJumpToCart = \PageModel::findByPk($this->iso_cart_jumpTo);

            if (null !== $objJumpToCart) {
                $arrButtons['cart'] = array(
                    'type'      => 'submit',
                    'name'      => 'button_cart',
                    'label'     => $GLOBALS['TL_LANG']['MSC']['cartBT'],
                    'href'      => \Controller::generateFrontendUrl($objJumpToCart->row()),
                );

                if (\Input::post('FORM_SUBMIT') == $this->strFormId && \Input::post('button_cart') != '') {
                    $this->jumpToOrReload($this->iso_cart_jumpTo);
                }
            }
        }

        // Add button to checkout page
        if ($this->iso_checkout_jumpTo > 0 && !Isotope::getCart()->hasErrors()) {
            $objJumpToCheckout = \PageModel::findByPk($this->iso_checkout_jumpTo);

            if (null !== $objJumpToCheckout) {
                $arrButtons['checkout'] = array(
                    'type'      => 'submit',
                    'name'      => 'button_checkout',
                    'label'     => $GLOBALS['TL_LANG']['MSC']['checkoutBT'],
                    'href'      => \Controller::generateFrontendUrl($objJumpToCheckout->row()),
                );

                if (\Input::post('FORM_SUBMIT') == $this->strFormId && \Input::post('button_checkout') != '') {
                    $this->jumpToOrReload($this->iso_checkout_jumpTo);
                }
            }
        }

        if ($this->iso_continueShopping && $this->Input->get('continue') != '') {
            $arrButtons['continue'] = array(
                'type'      => 'submit',
                'name'      => 'button_continue',
                'label'     => $GLOBALS['TL_LANG']['MSC']['continueShoppingBT'],
                'href'      => ampersand(base64_decode($this->Input->get('continue', true))),
            );

            if (\Input::post('FORM_SUBMIT') == $this->strFormId && \Input::post('button_continue') != '') {
                \Controller::redirect($arrButtons['continue']['href']);
            }
        }

        return $arrButtons;
    }
}
