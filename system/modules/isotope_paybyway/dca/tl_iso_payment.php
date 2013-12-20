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
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['paybyway'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},paybyway_merchant_id,paybyway_private_key;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paybyway_merchant_id'] = array
(
    'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paybyway_merchant_id'],
    'exclude'       => true,
    'inputType'     => 'text',
    'eval'          => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
    'sql'           => "int(10) NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paybyway_private_key'] = array
(
    'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paybyway_private_key'],
    'exclude'       => true,
    'inputType'     => 'text',
    'eval'          => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
    'sql'           => "varchar(255) NOT NULL default ''",
);
