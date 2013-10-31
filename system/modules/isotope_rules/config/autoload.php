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
NamespaceClassLoader::add('Isotope', 'system/modules/isotope_rules/library');


/**
 * Register classes outside the namespace folder
 */
NamespaceClassLoader::addClassMap(array
(
    // DCA Helpers
    'Isotope\tl_iso_rule'  => 'system/modules/isotope_rules/classes/tl_iso_rule.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'iso_coupons'    => 'system/modules/isotope_rules/templates',
));
