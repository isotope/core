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
define('TL_MODE', 'ISO_TEST');
require __DIR__.'/../../../initialize.php';

NamespaceClassLoader::add('Isotope\Test', 'system/modules/isotope/tests');

$GLOBALS['TL_LANGUAGE'] = 'en';
$GLOBALS['TL_CONFIG']['displayErrors'] = true;
\System::loadLanguageFile('default');