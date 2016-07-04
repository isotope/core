<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

define('TL_MODE', 'ISO_TEST');
define('TL_SCRIPT', 'system/modules/isotope/test/bootstrap.php');

require_once(__DIR__ . '/../initialize.php');

if (class_exists('NamespaceClassLoader')) {
    NamespaceClassLoader::add('Isotope\Test', 'system/modules/isotope/test/tests');
}

$GLOBALS['TL_LANGUAGE'] = 'en';
$GLOBALS['TL_CONFIG']['displayErrors'] = true;
\System::loadLanguageFile('default');
