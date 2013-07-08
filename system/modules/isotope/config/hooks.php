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


$GLOBALS['TL_HOOKS']['loadDataContainer'][]			= array('Isotope', 'loadProductsDataContainer');
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]			= array('Isotope', 'validateRegexp');
$GLOBALS['TL_HOOKS']['getSearchablePages'][]		= array('IsotopeFrontend', 'addProductsToSearchIndex');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]			= array('IsotopeFrontend', 'replaceIsotopeTags');
$GLOBALS['TL_HOOKS']['generatePage'][]				= array('IsotopeFrontend', 'injectMessages');
$GLOBALS['TL_HOOKS']['executePreActions'][]			= array('ProductTree', 'executePreActions');
$GLOBALS['TL_HOOKS']['executePostActions'][]		= array('ProductTree', 'executePostActions');
$GLOBALS['TL_HOOKS']['translateUrlParameters'][]	= array('IsotopeFrontend', 'translateProductUrls');
$GLOBALS['TL_HOOKS']['getSystemMessages'][]			= array('IsotopeBackend', 'getOrderMessages');
$GLOBALS['TL_HOOKS']['sqlGetFromFile'][]			= array('IsotopeBackend', 'addAttributesToDBUpdate');
$GLOBALS['TL_HOOKS']['getArticle'][]				= array('IsotopeFrontend', 'storeCurrentArticle');
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][]		= array('IsotopeFrontend', 'generateBreadcrumb');
$GLOBALS['ISO_HOOKS']['buttons'][]					= array('Isotope', 'defaultButtons');
$GLOBALS['ISO_HOOKS']['checkoutSurcharge'][]		= array('IsotopeFrontend', 'getShippingAndPaymentSurcharges');

if (TL_MODE == 'FE')
{
	// Do not parse backend templates
	$GLOBALS['TL_HOOKS']['parseTemplate'][]			= array('IsotopeFrontend', 'addNavigationClass');
}
