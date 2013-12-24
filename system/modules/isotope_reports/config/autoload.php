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


/**
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('Isotope', 'system/modules/isotope_reports/library');


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_iso_reports'                => 'system/modules/isotope_reports/templates',
    'iso_report_default'            => 'system/modules/isotope_reports/templates',
    'iso_report_sales_total'        => 'system/modules/isotope_reports/templates',
));
