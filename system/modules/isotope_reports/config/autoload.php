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
