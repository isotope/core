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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('{alexf_legend}', '{isotope_legend:hide},iso_modules,iso_product_types,iso_product_typep,iso_payment_modules,iso_payment_modulep,iso_shipping_modules,iso_shipping_modulep,iso_tax_classes,iso_tax_classp,iso_tax_rates,iso_tax_ratep,iso_mails,iso_mailp,iso_configs,iso_configp;{alexf_legend}', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_modules'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_modules'],
	'exclude'				=> true,
	'filter'				=> true,
	'inputType'				=> 'checkbox',
	'options_callback'		=> array('IsotopeBackend', 'getIsotopeModules'),
	'reference'				=> &$GLOBALS['TL_LANG']['IMD'],
	'eval'					=> array('multiple'=>true, 'helpwizard'=>true),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_product_types'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_product_types'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'foreignKey'			=> 'tl_iso_producttypes.name',
	'eval'					=> array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_product_typep'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_product_typep'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'options'				=> array('create', 'delete'),
	'reference'				=> &$GLOBALS['TL_LANG']['MSC'],
	'eval'					=> array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_payment_modules'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_payment_modules'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'foreignKey'			=> 'tl_iso_payment_modules.name',
	'eval'					=> array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_payment_modulep'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_payment_modulep'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'options'				=> array('create', 'delete'),
	'reference'				=> &$GLOBALS['TL_LANG']['MSC'],
	'eval'					=> array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_shipping_modules'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_shipping_modules'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'foreignKey'			=> 'tl_iso_shipping_modules.name',
	'eval'					=> array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_shipping_modulep'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_shipping_modulep'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'options'				=> array('create', 'delete'),
	'reference'				=> &$GLOBALS['TL_LANG']['MSC'],
	'eval'					=> array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_classes'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_tax_classes'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'foreignKey'			=> 'tl_iso_tax_class.name',
	'eval'					=> array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_classp'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_tax_classp'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'options'				=> array('create', 'delete'),
	'reference'				=> &$GLOBALS['TL_LANG']['MSC'],
	'eval'					=> array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_rates'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_tax_rates'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'foreignKey'			=> 'tl_iso_tax_rate.name',
	'eval'					=> array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_ratep'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_tax_ratep'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'options'				=> array('create', 'delete'),
	'reference'				=> &$GLOBALS['TL_LANG']['MSC'],
	'eval'					=> array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_mails'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_mails'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'foreignKey'			=> 'tl_iso_mail.name',
	'eval'					=> array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_mailp'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_mailp'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'options'				=> array('create', 'delete'),
	'reference'				=> &$GLOBALS['TL_LANG']['MSC'],
	'eval'					=> array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_configs'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_configs'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'foreignKey'			=> 'tl_iso_config.name',
	'eval'					=> array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_configp'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_user']['iso_configp'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'options'				=> array('create', 'delete'),
	'reference'				=> &$GLOBALS['TL_LANG']['MSC'],
	'eval'					=> array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

