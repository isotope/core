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
		'tables'					=> array('tl_iso_rules'),
		'stylesheet'				=> 'system/modules/isotope/html/backend.css',
		'javsacript'				=> 'system/modules/isotope/html/backend.js',
		'icon'						=> 'system/modules/isotope_rules/html/coupons.png',
		'delete'					=> array('IsotopeBackend', 'archiveRecord'),
	),
));


/**
 * Checkout Steps
 */
array_insert($GLOBALS['ISO_CHECKOUT_STEPS']['review'], 0, array(array('IsotopeRules', 'cleanRuleUsages')));


/**
 * Hooks
 */
$GLOBALS['ISO_HOOKS']['calculatePrice'][]				= array('IsotopeRules', 'calculatePrice');
$GLOBALS['ISO_HOOKS']['compileCart'][] 					= array('IsotopeRules', 'getCouponForm');
$GLOBALS['ISO_HOOKS']['checkoutSurcharge'][]			= array('IsotopeRules', 'getSurcharges');
$GLOBALS['ISO_HOOKS']['checkout'][]						= array('IsotopeRules', 'writeRuleUsages');

