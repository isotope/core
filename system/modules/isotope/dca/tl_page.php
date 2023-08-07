<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Input;
use Contao\PageModel;

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Callbacks
 */
$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = function(\DataContainer $dc) {
    if (Input::get('do') === 'page' && Input::get('table') === 'tl_page' && Input::get('field') === 'iso_readerJumpTo') {
        if (($objPage = PageModel::findWithDetails($dc->id)) !== null) {
            $GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerJumpTo']['rootNodes'] = array($objPage->rootId);
        }
    }
};

/**
 * Extend tl_page palettes
 */
try {
    PaletteManipulator::create()
        ->addLegend('isotope_legend', 'publish_legend', PaletteManipulator::POSITION_BEFORE)
        ->addField('iso_config', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
        ->addField('iso_store_id', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('root', 'tl_page')
        ->applyToPalette('rootfallback', 'tl_page');
} catch (\InvalidArgumentException $e) {}

PaletteManipulator::create()
    ->addLegend('isotope_legend', 'publish_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('iso_readerMode', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('regular', 'tl_page')
;


/**
 * Add a selector to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'iso_readerMode';


/**
 * Add subpalettes to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['iso_readerMode_page'] = 'iso_readerJumpTo';


/**
 * Add fields to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['iso_config'] = array
(
    'exclude'                 => true,
    'inputType'               => 'select',
    'foreignKey'              => \Isotope\Model\Config::getTable().'.name',
    'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_store_id'] = array
(
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'maxlength'=>2, 'tl_class'=>'w50'),
    'sql'                     => "int(2) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerMode'] = array
(
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => ['current', 'page', 'none'],
    'reference'               => &$GLOBALS['TL_LANG']['tl_page']['iso_readerModes'],
    'eval'                    => array('submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
    'explanation'             => 'isoReaderJumpTo',
    'sql'                     => "varchar(8) NOT NULL default 'current'",
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerJumpTo'] = array
(
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
);


/**
 * Disable header edit button
 */
if (($_GET['table'] ?? null) === \Isotope\Model\ProductCategory::getTable())
{
    $GLOBALS['TL_DCA']['tl_page']['config']['notEditable'] = true;
}
