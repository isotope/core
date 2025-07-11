<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Isotope\CoreBundle\Model\ProductCategory;
use Isotope\Model\Config;

$GLOBALS['TL_DCA']['tl_page']['config']['ctable'][] = ProductCategory::getTable();

/*
 * Callbacks
 */
$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = static function (DataContainer $dc): void {
    if ('page' === Input::get('do') && 'tl_page' === Input::get('table') && 'iso_readerJumpTo' === Input::get('field')) {
        if (($objPage = PageModel::findWithDetails($dc->id)) !== null) {
            $GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerJumpTo']['rootNodes'] = [$objPage->rootId];
        }
    }
};

/*
 * Extend tl_page palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['iso_products'] = $GLOBALS['TL_DCA']['tl_page']['palettes']['regular'];

PaletteManipulator::create()
    ->addLegend('isotope_legend', 'publish_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('iso_config', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_store_id', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page')
;

PaletteManipulator::create()
    ->addLegend('isotope_legend', 'meta_legend')
    ->addField('iso_readerMode', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('iso_products', 'tl_page')
;

/*
 * Add a selector to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'iso_readerMode';

/*
 * Add subpalettes to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['iso_readerMode_current'] = 'iso_sitemapVariants';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['iso_readerMode_page'] = 'iso_readerJumpTo,iso_sitemapVariants';

/*
 * Add fields to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['iso_config'] = [
    'inputType' => 'select',
    'foreignKey' => Config::getTable().'.name',
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_store_id'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'nospace' => true, 'maxlength' => 2, 'tl_class' => 'w50'],
    'sql' => "int(2) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerMode'] = [
    'inputType' => 'select',
    'options' => ['current', 'page', 'none'],
    'reference' => &$GLOBALS['TL_LANG']['tl_page']['iso_readerModes'],
    'eval' => ['submitOnChange' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
    'explanation' => 'isoReaderJumpTo',
    'sql' => "varchar(8) NOT NULL default 'current'",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerJumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_sitemapVariants'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default '1'",
];

/*
 * Disable header edit button
 */
if (($_GET['table'] ?? null) === ProductCategory::getTable()) {
    $GLOBALS['TL_DCA']['tl_page']['config']['notEditable'] = true;
}
