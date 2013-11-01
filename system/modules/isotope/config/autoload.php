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


/**
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('Isotope', 'system/modules/isotope/library');


/**
 * Register classes outside the namespace folder
 */
NamespaceClassLoader::addClassMap(array
(
    // Drivers
    'DC_ProductData'                    => 'system/modules/isotope/drivers/DC_ProductData.php',
    'DC_TablePageId'                    => 'system/modules/isotope/drivers/DC_TablePageId.php',

    // Helpers
    'Isotope\PasteProductButton'        => 'system/modules/isotope/helpers/PasteProductButton.php',
    'Isotope\ProductCallbacks'          => 'system/modules/isotope/helpers/ProductCallbacks.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_iso_introduction'           => 'system/modules/isotope/templates/backend',
    'be_iso_overview'               => 'system/modules/isotope/templates/backend',
    'be_iso_old'                    => 'system/modules/isotope/templates/backend',
    'iso_checkout_address'          => 'system/modules/isotope/templates/checkout',
    'iso_checkout_order_conditions' => 'system/modules/isotope/templates/checkout',
    'iso_checkout_order_info'       => 'system/modules/isotope/templates/checkout',
    'iso_checkout_payment_method'   => 'system/modules/isotope/templates/checkout',
    'iso_checkout_shipping_method'  => 'system/modules/isotope/templates/checkout',
    'iso_collection_default'        => 'system/modules/isotope/templates/collection',
    'iso_collection_mini'           => 'system/modules/isotope/templates/collection',
    'iso_document_default'          => 'system/modules/isotope/templates/document',
    'iso_filter_cumulative'         => 'system/modules/isotope/templates/isotope',
    'iso_filter_default'            => 'system/modules/isotope/templates/isotope',
    'iso_gallery_standard'          => 'system/modules/isotope/templates/gallery',
    'iso_gallery_inline'            => 'system/modules/isotope/templates/gallery',
    'iso_list_default'              => 'system/modules/isotope/templates/isotope',
    'iso_list_variants'             => 'system/modules/isotope/templates/isotope',
    'iso_payment_datatrans'         => 'system/modules/isotope/templates/payment',
    'iso_payment_paypal'            => 'system/modules/isotope/templates/payment',
    'iso_payment_payone'            => 'system/modules/isotope/templates/payment',
    'iso_payment_psp'               => 'system/modules/isotope/templates/payment',
    'iso_payment_worldpay'          => 'system/modules/isotope/templates/payment',
    'iso_reader_default'            => 'system/modules/isotope/templates/isotope',
    'mod_iso_addressbook'           => 'system/modules/isotope/templates/modules',
    'mod_iso_cart'                  => 'system/modules/isotope/templates/modules',
    'mod_iso_checkout'              => 'system/modules/isotope/templates/modules',
    'mod_iso_configswitcher'        => 'system/modules/isotope/templates/modules',
    'mod_iso_orderdetails'          => 'system/modules/isotope/templates/modules',
    'mod_iso_orderhistory'          => 'system/modules/isotope/templates/modules',
    'mod_iso_productlist'           => 'system/modules/isotope/templates/modules',
    'mod_iso_productlist_caching'   => 'system/modules/isotope/templates/modules',
    'mod_iso_productreader'         => 'system/modules/isotope/templates/modules',
));
