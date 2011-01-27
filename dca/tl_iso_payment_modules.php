<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Andreas Schempp 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['payone'] = '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},trans_type,payone_clearingtype,payone_aid,payone_portalid,payone_key;{price_legend:hide},price,tax_class;{enabled_legend},debug,enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['payone_clearingtype'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_clearingtype'],
	'inputType'		=> 'select',
	'options'		=> array('elv', 'cc', 'dc', 'vor', 'rec', 'sb', 'wlt'),
	'reference'		=> &$GLOBALS['TL_LANG']['MSG']['payone'],
	'eval'			=> array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['payone_aid'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_aid'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'maxlength'=>6, 'rgxp'=>'digit', 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['payone_portalid'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_portalid'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'maxlength'=>7, 'rgxp'=>'digit', 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['payone_key'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_key'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
);

