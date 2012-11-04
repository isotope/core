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
 * @author     Philipp Kaiblinger <philipp.kaiblinger@kaipo.at>
 */


/**
 * Isotope Modules
 */
$GLOBALS['ISO_MOD']['translation']['iso_labels'] = array
(
	'tables'	=> array('tl_iso_labels'),
	'icon'		=> 'system/modules/isotope_multilingual/assets/balloons.png',
);

$GLOBALS['ISO_MOD']['translation']['iso_translation'] = array
(
	'callback'	=> 'ModuleIsotopeTranslation',
	'icon'		=> 'system/modules/isotope_multilingual/assets/locale.png',
	'export'	=> array('ModuleIsotopeTranslation', 'export'),
);

$GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'][] = 'tl_iso_labels';


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadLanguageFile'][] = array('IsotopeTranslation', 'loadLocalLanguageFiles');

