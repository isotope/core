<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Register PSR-0 namespace
 */
if (class_exists('NamespaceClassLoader')) {
    NamespaceClassLoader::add('Isotope', 'system/modules/isotope_rules/library');
}


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'iso_coupons'     => 'system/modules/isotope_rules/templates',
    'mod_iso_coupons' => 'system/modules/isotope_rules/templates',
));
