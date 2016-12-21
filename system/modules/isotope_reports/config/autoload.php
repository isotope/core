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
    NamespaceClassLoader::add('Isotope', 'system/modules/isotope_reports/library');
}

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_iso_reports'                => 'system/modules/isotope_reports/templates',
    'iso_report_default'            => 'system/modules/isotope_reports/templates',
    'iso_report_sales_total'        => 'system/modules/isotope_reports/templates',
    'iso_report_members_guests'     => 'system/modules/isotope_reports/templates',
    'iso_block_panel'               => 'system/modules/isotope_reports/templates',
));
