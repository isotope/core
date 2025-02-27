<?php

/*
 * Table tl_iso_productcache
 */
$GLOBALS['TL_DCA']['tl_iso_productcache'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uniqid,requestcache_id,keywords,expires' => 'index',
                'page_id,module_id,requestcache_id,keywords,expires' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'uniqid' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'keywords' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'groups' => [
            'foreignKey' => 'tl_member_group.name',
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'products' => [
            'foreignKey' => 'tl_iso_product.name',
            'eval' => ['csv' => ','],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'expires' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'requestcache_id' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'page_id' => [
            'foreignKey' => 'tl_page.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'module_id' => [
            'foreignKey' => 'tl_module.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
    ],
];
