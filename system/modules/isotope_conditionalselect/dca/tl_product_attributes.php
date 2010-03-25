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
$GLOBALS['TL_DCA']['tl_product_attributes']['palettes']['conditionalselect'] = '{attribute_legend},name,field_name,type,legend,description;{options_legend},option_list,conditionField;{visibility_legend},is_listing_field,is_visible_on_front;{use_mode_legend},multilingual,is_customer_defined,is_multiple_select;{validation_legend},is_required;{search_filters_legend},is_filterable,is_order_by_enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_product_attributes']['fields']['conditionField'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_product_attributes']['conditionField'],
	'inputType'				=> 'select',
	'options_callback'		=> array('IsotopeConditionalSelect', 'getConditionFields'),
	'eval'					=> array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'clr'),
);

