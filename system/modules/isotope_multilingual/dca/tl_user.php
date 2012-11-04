<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend']	= str_replace(';{password_legend:hide}', ',translation;{password_legend:hide}', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['group']	= str_replace(';{password_legend:hide}', ',translation;{password_legend:hide}', $GLOBALS['TL_DCA']['tl_user']['palettes']['group']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend']	= str_replace(';{password_legend:hide}', ',translation;{password_legend:hide}', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom']	= str_replace(';{password_legend:hide}', ',translation;{password_legend:hide}', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['admin']	= str_replace(';{password_legend:hide}', ',translation;{password_legend:hide}', $GLOBALS['TL_DCA']['tl_user']['palettes']['admin']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['admin']['eval']['tl_class'] = 'w50';

$GLOBALS['TL_DCA']['tl_user']['fields']['translation'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['tl_user']['translation'],
	'inputType'	=> 'select',
	'options'	=> array_diff_key($this->getLanguages(), array('en'=>'English')),
	'eval'		=> array('includeBlankOption'=>true, 'tl_class'=>'w50'),
);

