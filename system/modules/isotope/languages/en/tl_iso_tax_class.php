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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name']						= array('Name', 'Give this tax class a name that explains what it is used for.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['fallback']					= array('Default', 'Check here if this is the default tax class.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['applyRoundingIncrement']	= array('Apply rounding increment', 'Check here if you want to apply the rounding increment of your shop config.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['includes']					= array('Tax rate included with product price', 'Select if prices of products with this tax class contain a tax rate. This tax rate will be subtracted from product price if it does not match.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['label']					= array('Include label', 'A label for orders to present for subtracted taxes (if included tax does not match). Default tax rate label will be used if this is blank.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rates']					= array('Apply tax rates', 'Add these tax rates to products with this tax class.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['new']    = array('New tax class', 'Create a New tax class');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['edit']   = array('Edit tax class', 'Edit tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['copy']   = array('Copy tax class', 'Copy tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['delete'] = array('Delete tax class', 'Delete tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['show']   = array('Order Details', 'Show details of tax class ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name_legend']	= 'Name';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rate_legend']	= 'Tax rates';
