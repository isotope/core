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


$GLOBALS['TL_HOOKS']['loadDataContainer'][]             = array('Isotope\Backend\Product\DcaManager', 'initialize');
$GLOBALS['TL_HOOKS']['getAttributesFromDca'][]          = array('Isotope\Backend\Product\DcaManager', 'addOptionsFromAttribute');
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]               = array('Isotope\Isotope', 'validateRegexp');
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][]              = array('Isotope\Frontend', 'loadReaderPageFromUrl');
$GLOBALS['TL_HOOKS']['getPageLayout'][]                 = array('Isotope\Frontend', 'overrideReaderPage');
$GLOBALS['TL_HOOKS']['getSearchablePages'][]            = array('Isotope\Frontend', 'addProductsToSearchIndex');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]             = array('Isotope\Frontend', 'replaceIsotopeTags');
$GLOBALS['TL_HOOKS']['generatePage'][]                  = array('Isotope\Frontend', 'injectScripts');
$GLOBALS['TL_HOOKS']['executePreActions'][]             = array('Isotope\Backend', 'executePreActions');
$GLOBALS['TL_HOOKS']['executePostActions'][]            = array('Isotope\Backend', 'executePostActions');
$GLOBALS['TL_HOOKS']['translateUrlParameters'][]        = array('Isotope\Frontend', 'translateProductUrls');
$GLOBALS['TL_HOOKS']['getSystemMessages'][]             = array('Isotope\Backend', 'getOrderMessages');
$GLOBALS['TL_HOOKS']['getArticle'][]                    = array('Isotope\Frontend', 'storeCurrentArticle');
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][]            = array('Isotope\Frontend', 'addProductToBreadcrumb');

if (TL_MODE == 'FE') {
    // Only limit countries in FE
    $GLOBALS['TL_HOOKS']['loadDataContainer'][]         = array('Isotope\Backend\Member\Callback', 'limitCountries');
}

if (TL_MODE == 'BE') {
    // Type agent help is only needed in back end
    $GLOBALS['TL_HOOKS']['loadDataContainer'][]         = array('Isotope\Backend', 'loadTypeAgentHelp');
    $GLOBALS['TL_HOOKS']['loadLanguageFile'][]          = array('Isotope\Backend\ProductType\Help', 'initializeWizard');

    // Adjust the product groups manager
    $GLOBALS['TL_HOOKS']['parseTemplate'][]             = array('Isotope\Backend', 'adjustGroupsManager');
}
