<?php

use Contao\DC_Table;
use Isotope\Model\Download;

/*
 * Table tl_iso_product_collection_download
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_download'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'closed' => true,
        'notEditable' => true,
        'ptable' => 'tl_iso_product_collection_item',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product_collection_item.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'download_id' => [
            'foreignKey' => Download::getTable().'.id',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'downloads_remaining' => [
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'expires' => [
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
