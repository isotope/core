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


class OrderProducts extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true to enable the module
     * @return  bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $objTemplate = new \Isotope\Template('iso_checkout_order_products');

        // Surcharges must be initialized before getProducts() to apply tax_id to each product
        $arrSurcharges = Isotope::getCart()->getSurcharges();
        $arrProductData = array();
        $arrProducts = Isotope::getCart()->getProducts();

        foreach ($arrProducts as $objProduct)
        {
            $arrProductData[] = array_merge($objProduct->getAttributes(), array
            (
                'id'                => $objProduct->id,
                'image'                => $objProduct->images->main_image,
                'link'                => $objProduct->href_reader,
                'price'                => Isotope::formatPriceWithCurrency($objProduct->price),
                'tax_free_price'    => Isotope::formatPriceWithCurrency($objProduct->tax_free_price),
                'total_price'        => Isotope::formatPriceWithCurrency($objProduct->total_price),
                'tax_free_total_price'    => Isotope::formatPriceWithCurrency($objProduct->tax_free_total_price),
                'quantity'            => $objProduct->quantity_requested,
                'tax_id'            => $objProduct->tax_id,
                'product_options'    => Isotope::formatOptions($objProduct->getOptions()),
            ));
        }

        $objTemplate->collection = Isotope::getCart();
        $objTemplate->products = \Isotope\Frontend::generateRowClass($arrProductData, 'row', 'rowClass', 0, ISO_CLASS_COUNT|ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);
        $objTemplate->surcharges = \Isotope\Frontend::formatSurcharges($arrSurcharges);
        $objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
        $objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
        $objTemplate->subTotalPrice = Isotope::formatPriceWithCurrency(Isotope::getCart()->getSubtotal());
        $objTemplate->grandTotalPrice = Isotope::formatPriceWithCurrency(Isotope::getCart()->getTotal());

        return $objTemplate->parse();
    }


    public function review()
    {
        return '';
    }
}
