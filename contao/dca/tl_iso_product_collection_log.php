<?php

use Isotope\Model\ProductCollection;

$GLOBALS['TL_DCA']['tl_iso_product_collection_log'] = [
    'config' => [
        'ptable' => ProductCollection::getTable(),
        'permissions' => [],
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
