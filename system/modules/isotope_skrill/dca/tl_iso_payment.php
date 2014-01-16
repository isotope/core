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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['skrill'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},skrill_pay_to_email,skrill_secret,skrill_parameters;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['skrill_pay_to_email'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['skrill_pay_to_email'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'maxlength'=>50, 'rgxp'=>'email', 'decodeEntities'=>true, 'tl_class'=>'w50'),
    'sql'                   => "varchar(50) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['skrill_secret'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['skrill_secret'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'hideInput'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
    'sql'                   => "varchar(10) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['skrill_parameters'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['skrill_parameters'],
    'exclude'               => true,
    'inputType'             => 'textarea',
    'eval'                  => array('decodeEntities'=>true, 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'clr', 'style="height:60px"'),
    'sql'                   => "text NULL",
    'explanation'           => 'skrill_parameters',
);
