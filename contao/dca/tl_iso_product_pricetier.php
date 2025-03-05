<?php

$GLOBALS['TL_DCA']['tl_iso_product_pricetier'] = [
    'config' => [
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
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'min' => [
            'sql' => "int(10) unsigned NOT NULL default '1'",
        ],
        'price' => [
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
    ],
];
