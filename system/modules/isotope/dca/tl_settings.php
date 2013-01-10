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
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Extend a tl_settings default palette
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace(';{chmod_legend', ',iso_cartTimeout;{chmod_legend', $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']);


/**
 * Add fields to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['iso_cartTimeout'] = array
(
    'label'					=> &$GLOBALS['TL_LANG']['tl_settings']['iso_cartTimeout'],
    'inputType'				=> 'text',
    'eval'					=> array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
);
