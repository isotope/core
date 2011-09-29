<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Leo Unglaub <leo.unglaub@iserv.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id: $
 */

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['__selector__'][] = 'datatrans_sign';
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['datatrans'] = '{type_legend},name,label,type;{config_legend},new_order_status,trans_type,postsale_mail,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},datatrans_id,datatrans_sign;{expert_legend:hide},guests,protected;{enabled_legend},enabled';


/**
 * Subpalettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['subpalettes']['datatrans_sign'] = 'datatrans_sign_value';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['datatrans_id'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_id'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array('mandatory'=>true, 'maxlength'=>100, 'rgxp'=>'digit', 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['datatrans_sign'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_sign'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array('tl_class'=>'clr', 'submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['datatrans_sign_value'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_sign_value'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array('mandatory'=>true, 'tl_class'=>'w50')
);
?>