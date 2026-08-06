<?php

$GLOBALS['TL_DCA']['tl_nc_notification']['palettes']['iso_order'] = '{title_legend},title,type;{config_legend},iso_collectionTpl,iso_gallery';
$GLOBALS['TL_DCA']['tl_nc_notification']['palettes']['iso_order_update'] = '{title_legend},title,type;{config_legend},iso_textCollectionTpl,iso_collectionTpl,iso_gallery';

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_collectionTpl'] = [
    'inputType' => 'select',
    'eval' => ['chosen' => true, 'tl_class' => 'w33'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_textCollectionTpl'] = [
    'inputType' => 'select',
    'eval' => ['chosen' => true, 'tl_class' => 'w33'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_gallery'] = [
    'inputType' => 'select',
    'foreignKey' => 'tl_iso_gallery.name',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w33'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
