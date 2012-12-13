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
 * Table tl_iso_config
 */
$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['__selector__'][] = 'ga_enable';
$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default'] .= ';{ga_legend},ga_enable';
$GLOBALS['TL_DCA']['tl_iso_config']['subpalettes']['ga_enable'] = 'ga_account,ga_trackMember';

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['ga_enable'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['ga_enable'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'						=> array('submitOnChange'=>true, 'doNotCopy'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['ga_account'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['ga_account'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>64, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['ga_trackMember'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['ga_trackMember'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50 m12'),
);
