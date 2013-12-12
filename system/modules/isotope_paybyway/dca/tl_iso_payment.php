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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['paybyway'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},paybyway_merchant_id,paybyway_secret_key;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paybyway_merchant_id'] = array
(
);
