<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Backend modules
 */
array_insert($GLOBALS['BE_MOD']['isotope'], 2, array
(
	'iso_rules' => array
	(
		'tables'					=> array('tl_iso_rule', 'tl_iso_rule_usage'), //'tl_iso_rule_codes'),
		'stylesheet'				=> 'system/modules/isotope/html/backend.css',
		'javsacript'				=> 'system/modules/isotope/html/backend.js',
		'icon'						=> 'system/modules/isotope_rules/html/coupons.png'
	),
));


/** 
 * Checkout Steps
 */
array_insert($GLOBALS['ISO_CHECKOUT_STEPS']['review'], 0, array(array('IsotopeRules', 'verifyRules')));


/** 
 * Hooks
 */
 
//called whenever products are loaded for lister, reader or product collection
$GLOBALS['TL_HOOKS']['iso_getProductUpdates'][] 			= array('IsotopeRules', 'getRules'); 

//used to display the extra rules data in the product template
$GLOBALS['TL_HOOKS']['iso_generateAttribute'][]				= array('IsotopeRules', 'updatePrice');
//$GLOBALS['TL_HOOKS']['iso_generateAjaxProduct'][]			= array('IsotopeRules', 'updateVariantPrice');

//called to retrieve the cart item id for caching this rule
$GLOBALS['TL_HOOKS']['iso_getProductCollectionInsertId'][] 	= array('IsotopeRules', 'addToCollection');

//used to recalculate the total discount, for example, if a product quantity changes
$GLOBALS['TL_HOOKS']['iso_updateProductInCollection'][] 	= array('IsotopeRules', 'updateProductInCollection');

//used to reflect the discounts in total for the cart
$GLOBALS['TL_HOOKS']['iso_getSurcharges'][]				= array('IsotopeRules', 'getSurcharges');

//used to retrieve a coupon form, if a valid coupon or coupons are available
$GLOBALS['TL_HOOKS']['iso_compileCart']['rules'] 			= array('IsotopeRules', 'getCouponForm');

//used to grab the surcharge data and display it in the cart (separate from actually updating the grand total
$GLOBALS['TL_HOOKS']['isoCheckoutSurcharge'][]				= array('IsotopeRules', 'calculateRuleTotals');

//$GLOBALS['TL_HOOKS']['iso_removeFromCart'][]				= array('IsotopeRules', 'removeFromCart');
