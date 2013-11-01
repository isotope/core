<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */


$GLOBALS['TL_HOOKS']['loadDataContainer'][]             = array('Isotope\Backend\DCA\tl_iso_product', 'loadProductsDCA');
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
$GLOBALS['ISO_HOOKS']['buttons'][]                      = array('Isotope\Isotope', 'defaultButtons');
$GLOBALS['ISO_HOOKS']['findSurchargesForCollection'][]  = array('Isotope\Frontend', 'findShippingAndPaymentSurcharges');

if (TL_MODE == 'FE') {
    // Only limit countries in FE
    $GLOBALS['TL_HOOKS']['loadDataContainer'][]        = array('Isotope\Backend\DCA\tl_member', 'limitCountries');
}

if (TL_MODE == 'BE') {
    // Type agent help is only needed in back end
    $GLOBALS['TL_HOOKS']['loadDataContainer'][]        = array('Isotope\Backend', 'loadTypeAgentHelp');

    // Adjust the product groups manager
    $GLOBALS['TL_HOOKS']['parseTemplate'][]            = array('Isotope\Backend', 'adjustGroupsManager');
}