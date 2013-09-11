<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['worldpay'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},worldpay_instId,worldpay_description;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['worldpay_instId'] = array
(
    'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['worldpay_instId'],
    'exclude'       => true,
    'inputType'     => 'text',
    'eval'          => array('mandatory'=>true, 'maxlength'=>6, 'rgxp'=>'digit'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['worldpay_description'] = array
(
    'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['worldpay_description'],
    'exclude'       => true,
    'inputType'     => 'text',
    'eval'          => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
);
