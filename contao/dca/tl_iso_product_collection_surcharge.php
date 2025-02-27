<?php

/*
 * Table tl_iso_product_collection_surcharge
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_surcharge'] = [
    'config' => [
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
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'source_id' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'label' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'price' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'total_price' => [
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'tax_free_total_price' => [
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'tax_class' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tax_id' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'before_tax' => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addToTotal' => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'applyRoundingIncrement' => [
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'products' => [
            'sql' => 'blob NULL',
        ],
    ],
];
