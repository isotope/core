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


$GLOBALS['TL_HOOKS']['loadDataContainer'][]             = array('Isotope\tl_iso_products', 'loadProductsDCA');
$GLOBALS['TL_HOOKS']['loadDataContainer'][]             = array('Isotope\tl_member', 'limitCountries');
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]               = array('Isotope\Isotope', 'validateRegexp');
$GLOBALS['TL_HOOKS']['getSearchablePages'][]            = array('Isotope\Frontend', 'addProductsToSearchIndex');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]             = array('Isotope\Frontend', 'replaceIsotopeTags');
$GLOBALS['TL_HOOKS']['generatePage'][]                  = array('Isotope\Frontend', 'injectMessages');
$GLOBALS['TL_HOOKS']['executePreActions'][]             = array('Isotope\Widget\ProductTree', 'executePreActions');
$GLOBALS['TL_HOOKS']['executePostActions'][]            = array('Isotope\Widget\ProductTree', 'executePostActions');
$GLOBALS['TL_HOOKS']['translateUrlParameters'][]        = array('Isotope\Frontend', 'translateProductUrls');
$GLOBALS['TL_HOOKS']['getSystemMessages'][]             = array('Isotope\Backend', 'getOrderMessages');
$GLOBALS['TL_HOOKS']['sqlGetFromFile'][]                = array('Isotope\Backend', 'addAttributesToDBUpdate');
$GLOBALS['TL_HOOKS']['getArticle'][]                    = array('Isotope\Frontend', 'storeCurrentArticle');
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][]            = array('Isotope\Frontend', 'generateBreadcrumb');
$GLOBALS['ISO_HOOKS']['buttons'][]                      = array('Isotope\Isotope', 'defaultButtons');
$GLOBALS['ISO_HOOKS']['findSurchargesForCollection'][]  = array('Isotope\Frontend', 'findShippingAndPaymentSurcharges');

if (TL_MODE == 'FE')
{
    // Do not parse backend templates
    $GLOBALS['TL_HOOKS']['parseTemplate'][]            = array('Isotope\Frontend', 'addNavigationClass');
}
