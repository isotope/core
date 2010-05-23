<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_attributes']['palettes']['imageselect'] = '{attribute_legend},name,field_name,type,legend,description;{options_legend},imageSource,size,sortBy;{visibility_legend},is_listing_field,is_visible_on_front;{use_mode_legend},multilingual,is_customer_defined,add_to_product_variants,is_multiple_select;{validation_legend},is_required;{search_filters_legend},is_filterable,is_order_by_enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['imageSource'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['imageSource'],
	'inputType'				=> 'fileTree',
	'eval'					=> array('fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['size'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['size'],
	'inputType'				=> 'imageSize',
	'options'				=> array('crop', 'proportional', 'box'),
	'reference'				=> &$GLOBALS['TL_LANG']['MSC'],
	'eval'					=> array('rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['sortBy'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['sortBy'],
	'inputType'				=> 'select',
	'options'				=> array('name_asc', 'name_desc', 'date_asc', 'date_desc', 'meta'),
	'reference'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes'],
	'eval'					=> array('tl_class'=>'w50')
);

