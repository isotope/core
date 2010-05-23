<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005-2009 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


/**
 * Backend modules
 */
array_insert($GLOBALS['BE_MOD']['isotope'], 1, array
(
	'iso_dimensions' => array
	(
		'tables'		=> array('tl_product_dimensions', 'tl_product_dimension_prices'),
		'icon'			=> 'system/modules/isotope_dimensions/html/icon-dimensions.png',
	),
));


/**
 * Product types
 */
$GLOBALS['ISO_PRODUCT']['dimension'] = array
(
	'class'				=> 'DimensionProduct',
	'disabledFields'	=> array('price', 'price_override'),
);

$GLOBALS['ISO_PRODUCT']['regular']['disabledFields'][] = 'dimensions';
$GLOBALS['ISO_PRODUCT']['regular']['disabledFields'][] = 'dimensions_min';
$GLOBALS['ISO_PRODUCT']['regular']['disabledFields'][] = 'dimensions_max';

