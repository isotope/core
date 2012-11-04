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
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Backend modules
 */
array_insert($GLOBALS['BE_MOD']['isotope'], 2, array
(
	'iso_rules' => array
	(
		'tables'					=> array('tl_iso_rules'),
		'javsacript'				=> 'system/modules/isotope/assets/backend.min.js',
		'icon'						=> 'system/modules/isotope_rules/assets/auction-hammer-gavel.png'
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
$GLOBALS['ISO_HOOKS']['preCheckout'][]					= array('IsotopeRules', 'writeRuleUsages');
$GLOBALS['ISO_HOOKS']['transferredCollection'][]		= array('IsotopeRules', 'transferCoupons');

