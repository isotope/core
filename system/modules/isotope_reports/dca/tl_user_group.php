<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$this->loadLanguageFile(\Isotope\Model\Group::getTable());
$this->loadDataContainer(\UserModel::getTable());

/**
 * Extend a tl_user_group palette
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace(',iso_modules,', ',iso_modules,iso_reports,', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_reports'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iso_reports'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options_callback'        => array('tl_iso_reports', 'getReports'),
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h'),
);
