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
 
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_quickproducts']	= '{type_legend},type,headline;{include_legend},productsAlias;{config_legend},iso_reader_jumpTo;{template_legend},iso_list_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['productsAlias'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['productsAlias'],
	'exclude'					=> true,
	'inputType'					=> 'tableLookup',
	'eval' => array
	(
		'mandatory'				=> true,
		'tl_class'				=> 'clr',
		'foreignTable'			=> 'tl_iso_products',
		'listFields'			=> array('type'=>'(SELECT name FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id)', 'name', 'sku'),
		'searchFields'			=> array('name', 'alias', 'sku', 'description'),
		'sqlWhere'				=> 'pid=0',
		'searchLabel'			=> 'Search products',
	)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_reader_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_reader_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio'),
);

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_list_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_list_layout'],
	'default'                 => 'iso_list_default',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => (version_compare(VERSION.BUILD, '2.9.0', '>=')) ? $this->getTemplateGroup('iso_list_', $dc->activeRecord->pid) : $this->getTemplateGroup('iso_list_'),
	'eval'					  => array('includeBlankOption'=>true),
);

