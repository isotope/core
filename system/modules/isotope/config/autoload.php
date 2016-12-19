<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */


if (class_exists('NamespaceClassLoader')) {
    /**
     * Register PSR-0 namespace
     */
    NamespaceClassLoader::add('Isotope', 'system/modules/isotope/library');
    NamespaceClassLoader::add('UnitedPrototype', 'system/modules/isotope/library');

    /**
     * Register classes outside the namespace folder
     */
    NamespaceClassLoader::addClassMap(
        array
        (
            // Drivers
            'DC_ProductData' => 'system/modules/isotope/drivers/DC_ProductData.php',
            'DC_TablePageId' => 'system/modules/isotope/drivers/DC_TablePageId.php',
        )
    );
}


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_iso_integrity'                  => 'system/modules/isotope/templates/backend',
    'be_iso_introduction'               => 'system/modules/isotope/templates/backend',
    'be_iso_overview'                   => 'system/modules/isotope/templates/backend',
    'be_iso_old'                        => 'system/modules/isotope/templates/backend',
    'be_iso_upgrade'                    => 'system/modules/isotope/templates/backend',
    'iso_checkout_address'              => 'system/modules/isotope/templates/checkout',
    'iso_checkout_order_conditions'     => 'system/modules/isotope/templates/checkout',
    'iso_checkout_order_info'           => 'system/modules/isotope/templates/checkout',
    'iso_checkout_payment_method'       => 'system/modules/isotope/templates/checkout',
    'iso_checkout_shipping_method'      => 'system/modules/isotope/templates/checkout',
    'iso_collection_default'            => 'system/modules/isotope/templates/collection',
    'iso_collection_mini'               => 'system/modules/isotope/templates/collection',
    'iso_collection_invoice'            => 'system/modules/isotope/templates/collection',
    'iso_collection_favorites'          => 'system/modules/isotope/templates/collection',
    'iso_document_default'              => 'system/modules/isotope/templates/document',
    'iso_filter_default'                => 'system/modules/isotope/templates/isotope',
    'iso_gallery_standard'              => 'system/modules/isotope/templates/gallery',
    'iso_gallery_inline'                => 'system/modules/isotope/templates/gallery',
    'iso_gallery_elevatezoom'           => 'system/modules/isotope/templates/gallery',
    'iso_list_default'                  => 'system/modules/isotope/templates/isotope',
    'iso_list_variants'                 => 'system/modules/isotope/templates/isotope',
    'iso_payment_concardis'             => 'system/modules/isotope/templates/payment',
    'iso_payment_datatrans'             => 'system/modules/isotope/templates/payment',
    'iso_payment_innopay'               => 'system/modules/isotope/templates/payment',
    'iso_payment_epay'                  => 'system/modules/isotope/templates/payment',
    'iso_payment_paybyway'              => 'system/modules/isotope/templates/payment',
    'iso_payment_paypal'                => 'system/modules/isotope/templates/payment',
    'iso_payment_payone'                => 'system/modules/isotope/templates/payment',
    'iso_payment_postfinance'           => 'system/modules/isotope/templates/payment',
    'iso_payment_quickpay'              => 'system/modules/isotope/templates/payment',
    'iso_payment_sofortueberweisung'    => 'system/modules/isotope/templates/payment',
    'iso_payment_viveum'                => 'system/modules/isotope/templates/payment',
    'iso_payment_worldpay'              => 'system/modules/isotope/templates/payment',
    'iso_payment_opp'                   => 'system/modules/isotope/templates/payment',
    'iso_reader_default'                => 'system/modules/isotope/templates/isotope',
    'iso_scripts'                       => 'system/modules/isotope/templates/isotope',
    'mod_iso_addressbook'               => 'system/modules/isotope/templates/modules',
    'mod_iso_favorites'                 => 'system/modules/isotope/templates/modules',
    'mod_iso_wishlist'                  => 'system/modules/isotope/templates/modules',
    'mod_iso_wishlists'                 => 'system/modules/isotope/templates/modules',
    'mod_iso_cart'                      => 'system/modules/isotope/templates/modules',
    'mod_iso_checkout'                  => 'system/modules/isotope/templates/modules',
    'mod_iso_configswitcher'            => 'system/modules/isotope/templates/modules',
    'mod_iso_cumulativefilter'          => 'system/modules/isotope/templates/modules',
    'mod_iso_orderdetails'              => 'system/modules/isotope/templates/modules',
    'mod_iso_orderhistory'              => 'system/modules/isotope/templates/modules',
    'mod_iso_messages'                  => 'system/modules/isotope/templates/modules',
    'mod_iso_productlist'               => 'system/modules/isotope/templates/modules',
    'mod_iso_productlist_caching'       => 'system/modules/isotope/templates/modules',
    'mod_iso_productreader'             => 'system/modules/isotope/templates/modules',
    'mod_iso_shipping_calculator'       => 'system/modules/isotope/templates/modules',
));
