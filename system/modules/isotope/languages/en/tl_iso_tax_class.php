<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name']                     = array('Name', 'Give this tax class a name that explains what it is used for.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['fallback']                 = array('Default', 'Check here if this is the default tax class.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['includes']                 = array('Tax rate included with product price', 'Select if prices of products with this tax class contain a tax rate. This tax rate will be subtracted from product price if it does not match.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['label']                    = array('Include label', 'A label for orders to present for subtracted taxes (if included tax does not match). Default tax rate label will be used if this is blank.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rates']                    = array('Apply tax rates', 'Add these tax rates to products with this tax class.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['applyRoundingIncrement']   = array('Apply rounding increment', 'Check here if you want to apply the rounding increment of your shop config.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['notNegative']              = array('Not negative', 'Prevents negative value for this tax (negative amount will be adjusted to 0.00).');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['new']                      = array('New tax class', 'Create a new tax class');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['edit']                     = array('Edit tax class', 'Edit tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['copy']                     = array('Copy tax class', 'Copy tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['delete']                   = array('Delete tax class', 'Delete tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['show']                     = array('Tax class details', 'Show details of tax class ID %s');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name_legend']              = 'Name';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rate_legend']              = 'Tax rates';
