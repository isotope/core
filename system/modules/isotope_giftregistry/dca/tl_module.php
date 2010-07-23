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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_registry_manager']	= '{title_legend},name,headline,type;iso_registry_layout;guests,protected;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_registry_search']	= '{title_legend},name,headline,type;jumpTo;guests,protected;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_registry_reader']	= '{title_legend},name,headline,type;iso_list_layout,iso_cart_jumpTo,iso_buttons,iso_use_quantity;guests,protected;align,space,cssID';

$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist'] = str_replace('iso_addProductJumpTo','iso_addProductJumpTo,iso_registry_jumpTo',$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist']);

$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productreader'] = str_replace('iso_addProductJumpTo','iso_addProductJumpTo,iso_registry_jumpTo',$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productreader']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_registry_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_registry_layout'],
	'default'                 => 'iso_registry_manage',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_registry_manage')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_registry_reader'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_registry_reader'],
	'default'                 => 'iso_registry_full',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_registry_full')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_registry_results'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_registry_results_lister'],
	'default'                 => 'iso_registry_searchlister',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_registry_search')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_registry_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_registry_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio')
);

