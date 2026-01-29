<?php

use Isotope\Backend;

$GLOBALS['TL_DCA']['tl_nc_notification']['palettes']['iso_order_status_change'] = '{title_legend},title,type;{config_legend},iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_document;{templates_legend:hide},templates';

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_collectionTpl'] = [
    'inputType' => 'select',
    'options_callback' => static fn () => Backend::getTemplates('iso_collection_'),
    'eval' => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_orderCollectionBy'] = [
    'default' => 'asc_id',
    'inputType' => 'select',
    'options' => &$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
    'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_gallery'] = [
    'inputType' => 'select',
    'foreignKey' => 'tl_iso_gallery.name',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_document'] = [
    'inputType' => 'select',
    'foreignKey' => 'tl_iso_document.name',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
