<?php

use Isotope\Model\Download;

$GLOBALS['TL_DCA']['tl_iso_product_collection_download'] = [
    'config' => [
        'ptable' => 'tl_iso_product_collection_item',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
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
