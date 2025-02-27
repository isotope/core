<?php

use Contao\DC_Table;
use Isotope\Model\ProductCollection;

/*
 * Table tl_iso_product_collection_log
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_log'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => ProductCollection::getTable(),
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
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'author' => [
            'foreignKey' => 'tl_user.username',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'data' => [
            'sql' => 'blob NULL',
        ],
    ],
];
