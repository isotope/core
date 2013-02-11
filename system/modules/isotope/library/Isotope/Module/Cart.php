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

        // Do not index or cache the page
        global $objPage;
        $objPage->noSearch = 1;
        $objPage->cache = 0;

        return parent::generate();
    }


    /**
     * Generate module
     * @return void
     */
    protected function compile()
    {
        $arrProducts = $this->Isotope->Cart->getProducts();

        if (empty($arrProducts))
        {
            $this->Template->empty = true;
            $this->Template->type = 'empty';
            $this->Template->message = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];

            return;
        }

        $objTemplate = new \Isotope\Template($this->iso_cart_layout);

        global $objPage;
        $strUrl = $this->generateFrontendUrl($objPage->row());

        $blnReload = false;
        $arrQuantity = \Input::post('quantity');
        $arrProductData = array();

        // Surcharges must be initialized before getProducts() to apply tax_id to each product
        $arrSurcharges = $this->Isotope->Cart->getSurcharges();

        $arrProducts = $this->Isotope->Cart->getProducts();
        $lastAdded = ($this->iso_continueShopping && !empty($_SESSION['ISO_CONFIRM'])) ? $this->Isotope->Cart->lastAdded : 0;

        foreach ($arrProducts as $i => $objProduct)
        {
            // Remove product from cart
            if (\Input::get('remove') == $objProduct->cart_id && $this->Isotope->Cart->deleteProduct($objProduct))
            {
                $this->redirect((strlen(\Input::get('referer')) ? base64_decode(\Input::get('referer', true)) : $strUrl));
            }

            // Update cart data if form has been submitted
            elseif (\Input::post('FORM_SUBMIT') == ('iso_cart_update_'.$this->id) && is_array($arrQuantity))
            {
                $blnReload = true;
                $this->Isotope->Cart->updateProduct($objProduct, array('quantity'=>$arrQuantity[$objProduct->cart_id]));
                continue; // no need to generate $arrProductData, we reload anyway
            }

            $arrProductData[] = array_merge($objProduct->getAttributes(), array
            (
                'id'				=> $objProduct->id,
                'image'				=> $objProduct->images->main_image,
                'link'				=> $objProduct->href_reader,
                'original_price'	=> $this->Isotope->formatPriceWithCurrency($objProduct->original_price),
                'price'				=> $this->Isotope->formatPriceWithCurrency($objProduct->price),
                'tax_free_price'	=> $this->Isotope->formatPriceWithCurrency($objProduct->tax_free_price),
                'total_price'		=> $this->Isotope->formatPriceWithCurrency($objProduct->total_price),
                'tax_free_total_price'	=> $this->Isotope->formatPriceWithCurrency($objProduct->tax_free_total_price),
                'tax_id'			=> $objProduct->tax_id,
                'quantity'			=> $objProduct->quantity_requested,
                'cart_item_id'		=> $objProduct->cart_id,
                'product_options'	=> $objProduct->getOptions(),
                'remove_link'		=> ampersand($strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?') . 'remove='.$objProduct->cart_id.'&referer='.base64_encode(\Environment::get('request'))),
                'remove_link_text'  => $GLOBALS['TL_LANG']['MSC']['removeProductLinkText'],
                'remove_link_title' => specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $objProduct->name)),
            ));

            if ($lastAdded == $objProduct->cart_id)
            {
                $objTemplate->continueJumpTo = $objProduct->href_reader;
            }
        }

        $blnInsufficientSubtotal = ($this->Isotope->Config->cartMinSubtotal > 0 && $this->Isotope->Config->cartMinSubtotal > $this->Isotope->Cart->subTotal) ? true : false;

        // Redirect if the "checkout" button has been submitted and minimum order total is reached
        if ($blnReload && \Input::post('checkout') != '' && !$blnInsufficientSubtotal)
        {
            $this->jumpToOrReload($this->iso_checkout_jumpTo);
        }

        // Otherwise, just reload the page
        elseif ($blnReload)
        {
            $this->reload();
        }

        // !HOOK: add additional forms into the template
        if (isset($GLOBALS['ISO_HOOKS']['compileCart']) && is_array($GLOBALS['ISO_HOOKS']['compileCart']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['compileCart'] as $name => $callback)
            {
                $this->import($callback[0]);
                $strForm = $this->$callback[0]->$callback[1]($this, $objTemplate, $arrProductData, $arrSurcharges);

                if ($strForm !== false)
                {
                     $arrForms[$name] = $strForm;
                }
            }
        }

        $objTemplate->hasError = $blnInsufficientSubtotal ? true : false;
        $objTemplate->minSubtotalError = sprintf($GLOBALS['TL_LANG']['ERR']['cartMinSubtotal'], $this->Isotope->formatPriceWithCurrency($this->Isotope->Config->cartMinSubtotal));
        $objTemplate->formId = 'iso_cart_update_'.$this->id;
        $objTemplate->formSubmit = 'iso_cart_update_'.$this->id;
        $objTemplate->summary = $GLOBALS['TL_LANG']['MSC']['cartSummary'];
        $objTemplate->action = \Environment::get('request');
        $objTemplate->cartJumpTo = $this->iso_cart_jumpTo ? $this->generateFrontendUrl($this->Database->execute("SELECT * FROM tl_page WHERE id={$this->iso_cart_jumpTo}")->fetchAssoc()) : '';
        $objTemplate->cartLabel = $GLOBALS['TL_LANG']['MSC']['cartBT'];
        $objTemplate->checkoutJumpToLabel = $GLOBALS['TL_LANG']['MSC']['checkoutBT'];
        $objTemplate->checkoutJumpTo = ($this->iso_checkout_jumpTo && !$blnInsufficientSubtotal) ? $this->generateFrontendUrl($this->Database->execute("SELECT * FROM tl_page WHERE id={$this->iso_checkout_jumpTo}")->fetchAssoc()) : '';
        $objTemplate->continueLabel = $GLOBALS['TL_LANG']['MSC']['continueShoppingBT'];

        $objTemplate->collection = $this->Isotope->Cart;
        $objTemplate->products = \Isotope\Frontend::generateRowClass($arrProductData, 'row', 'rowClass', 0, ISO_CLASS_COUNT|ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);
        $objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
        $objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
        $objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->subTotal);
        $objTemplate->grandTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->grandTotal);
        // @todo make a module option.
        $objTemplate->showOptions = false;
        $objTemplate->surcharges = \Isotope\Frontend::formatSurcharges($arrSurcharges);
        $objTemplate->forms = $arrForms;

        $this->Template->empty = false;
        $this->Template->cart = $objTemplate->parse();
    }
}
