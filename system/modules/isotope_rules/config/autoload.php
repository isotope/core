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
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Classes
    'Isotope\Rules'         => 'system/modules/isotope_rules/classes/Rules.php',
    'Isotope\tl_iso_rules'  => 'system/modules/isotope_rules/classes/tl_iso_rules.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'iso_coupons'    => 'system/modules/isotope_rules/templates',
));
