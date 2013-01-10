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
    'Isotope\IsotopeTranslation'             => 'system/modules/isotope_multilingual/classes/IsotopeTranslation.php',

    // Modules
    'Isotope\ModuleIsotopeTranslation'       => 'system/modules/isotope_multilingual/modules/ModuleIsotope.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_isotope_translation'    => 'system/modules/isotope_multilingual/templates',
));
