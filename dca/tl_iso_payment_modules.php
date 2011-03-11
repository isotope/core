<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *
 * PHP version 5
 * @copyright  Rispler&Rispler Designer Partnerschaftsgesellschaft 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    commercial
 * @version    $Id$
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['expercash'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},expercash_popupId,expercash_profile,expercash_popupKey,expercash_paymentMethod;{price_legend:hide},price,tax_class;{template_legend},expercash_css;{expert_legend:hide},guests,protected;{enabled_legend},enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['expercash_popupId'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_popupId'],
	'inputType'			=> 'text',
	'eval'				=> array('mandatory'=>true, 'maxlength'=>8, 'decodeEntities'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['expercash_profile'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_profile'],
	'inputType'			=> 'text',
	'eval'				=> array('mandatory'=>true, 'maxlength'=>3, 'rgxp'=>'digit', 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['expercash_popupKey'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_popupKey'],
	'inputType'			=> 'text',
	'eval'				=> array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['expercash_paymentMethod'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod'],
	'inputType'			=> 'select',
	'options'			=> array('automatic_payment_method', 'elv_buy', 'elv_authorize', 'cc_buy', 'cc_authorize', 'giropay', 'sofortueberweisung'),
	'reference'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod'],
	'eval'				=> array('mandatory'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['expercash_css'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_css'],
	'inputType'			=> 'fileTree',
	'eval'				=> array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'css', 'tl_class'=>'clr'),
);

