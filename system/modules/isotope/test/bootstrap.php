<?php

/**
 * Initialize Contao
 */
define('TL_MODE', 'ISO_TEST');
require __DIR__.'/../../../initialize.php';

NamespaceClassLoader::add('Isotope\Test', 'system/modules/isotope/tests');

$GLOBALS['TL_LANGUAGE'] = 'en';
$GLOBALS['TL_CONFIG']['displayErrors'] = true;
\System::loadLanguageFile('default');