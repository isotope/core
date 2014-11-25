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


/**
 * Backend module
 */
$GLOBALS['TL_LANG']['tl_iso_integrity']['headline'] = 'Integrity Check';
$GLOBALS['TL_LANG']['tl_iso_integrity']['columns'] = array('Problem', 'Result of the check');
$GLOBALS['TL_LANG']['tl_iso_integrity']['description'] = 'Please choose the errors to be fixed.';
$GLOBALS['TL_LANG']['tl_iso_integrity']['action'] = 'Resolve problems';
$GLOBALS['TL_LANG']['tl_iso_integrity']['permission'] = 'Only admin can perform integrity check.';

/**
 * Integrity checks
 */
$GLOBALS['TL_LANG']['tl_iso_integrity']['pricetable'][0] = 'Advanced prices';
$GLOBALS['TL_LANG']['tl_iso_integrity']['pricetable'][1] = '%s invalid andvanced prices were found.<br>PIDs: %s.';
$GLOBALS['TL_LANG']['tl_iso_integrity']['pricetable'][2] = 'There are no invalid prices in your database.';
$GLOBALS['TL_LANG']['tl_iso_integrity']['variantorphans'][0] = 'Invalid product variants';
$GLOBALS['TL_LANG']['tl_iso_integrity']['variantorphans'][1] = 'You have %s variants that belong to products without active variant support.<br>IDs: %s';
$GLOBALS['TL_LANG']['tl_iso_integrity']['variantorphans'][2] = 'There are no invalid variants in your database.';
$GLOBALS['TL_LANG']['tl_iso_integrity']['attributeoptionorphans'][0] = 'Invalid attribute options';
$GLOBALS['TL_LANG']['tl_iso_integrity']['attributeoptionorphans'][1] = 'You have %s attribute options that belong to non-existant variants or products or have a wrong optionsSource set.<br>IDs: %s';
$GLOBALS['TL_LANG']['tl_iso_integrity']['attributeoptionorphans'][2] = 'There are no invalid attribute options in your database.';
$GLOBALS['TL_LANG']['tl_iso_integrity']['unusedrules'][0] = 'Rules module';
$GLOBALS['TL_LANG']['tl_iso_integrity']['unusedrules'][1] = 'The rules module should be disabled if you do not use it.';
$GLOBALS['TL_LANG']['tl_iso_integrity']['unusedrules'][2] = 'The rules module is disabled or in use.';
