<?php

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
    if (\Input::get('do') == 'page' && \Input::get('table') == 'tl_page' && \Input::get('field') == 'iso_readerJumpTo') {
        if (($objPage = \PageModel::findWithDetails($dc->id)) !== null) {
            $GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerJumpTo']['rootNodes'] = array($objPage->rootId);
        }
    }
};

/**
 * Extend tl_page palettes
 */
\Haste\Dca\PaletteManipulator::create()
    ->addLegend('isotope_legend', 'publish_legen', \Haste\Dca\PaletteManipulator::POSITION_BEFORE)
    ->addField('iso_config', 'isotope_legend', \Haste\Dca\PaletteManipulator::POSITION_APPEND)
    ->addField('iso_store_id', 'isotope_legend', \Haste\Dca\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
;

\Haste\Dca\PaletteManipulator::create()
    ->addLegend('isotope_legend', 'publish_legen', \Haste\Dca\PaletteManipulator::POSITION_BEFORE)
    ->addField('iso_setReaderJumpTo', 'isotope_legend', \Haste\Dca\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('regular', 'tl_page')
;


/**
 * Add a selector to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'iso_setReaderJumpTo';


/**
 * Add subpalettes to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['iso_setReaderJumpTo'] = 'iso_readerJumpTo';


/**
 * Add fields to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['iso_config'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_config'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'foreignKey'              => \Isotope\Model\Config::getTable().'.name',
    'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_store_id'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_store_id'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'maxlength'=>2, 'tl_class'=>'w50'),
    'sql'                     => "int(2) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_setReaderJumpTo'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_setReaderJumpTo'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
    'sql'                     => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerJumpTo'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_readerJumpTo'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'explanation'             => 'isoReaderJumpTo',
    'eval'                    => array('fieldType'=>'radio', 'mandatory'=>true, 'helpwizard'=>true),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
);


/**
 * Disable header edit button
 */
if ($_GET['table'] == \Isotope\Model\ProductCategory::getTable())
{
    $GLOBALS['TL_DCA']['tl_page']['config']['notEditable'] = true;
}
