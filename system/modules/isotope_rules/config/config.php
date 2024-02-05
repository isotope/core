<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

 use Contao\ArrayUtil;

/**
 * Backend modules
 */
ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['isotope'], 2, array
(
    'iso_rules' => array
    (
        'tables'        => array(\Isotope\Model\Rule::getTable()),
        'javascript'    => \Haste\Util\Debug::uncompressedFile('system/modules/isotope/assets/js/backend.min.js'),
        'icon'          => 'system/modules/isotope_rules/assets/auction-hammer-gavel.png'
    ),
));


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope']['iso_coupons'] = 'Isotope\Module\Coupons';


/**
 * Models
 */
$GLOBALS['TL_MODELS'][\Isotope\Model\Rule::getTable()] = 'Isotope\Model\Rule';


/**
 * Checkout Steps
 * @todo this will no longer work
 */
ArrayUtil::arrayInsert($GLOBALS['ISO_CHECKOUT_STEPS']['review'], 0, array(array('Isotope\Rules', 'cleanRuleUsages')));


/**
 * Product collection surcharge
 */
\Isotope\Model\ProductCollectionSurcharge::registerModelType('rule', 'Isotope\Model\ProductCollectionSurcharge\Rule');


/**
 * Hooks
 */
$GLOBALS['ISO_HOOKS']['calculatePrice'][]               = array('Isotope\Rules', 'calculatePrice');
$GLOBALS['ISO_HOOKS']['compileCart'][]                  = array('Isotope\Rules', 'getCouponForm');
$GLOBALS['ISO_HOOKS']['findSurchargesForCollection'][]  = array('Isotope\Rules', 'findSurcharges');
$GLOBALS['ISO_HOOKS']['preCheckout'][]                  = array('Isotope\Rules', 'writeRuleUsages');
$GLOBALS['ISO_HOOKS']['copiedCollectionItems'][]        = array('Isotope\Rules', 'transferCoupons');
$GLOBALS['ISO_HOOKS']['postDeleteCollection'][]         = array('Isotope\Rules', 'deleteRuleUsages');
