<?php

$GLOBALS['TL_DCA']['tl_iso_requestcache'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'id,store_id' => 'index',
                'store_id,config_hash' => 'index',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'store_id' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'config_hash' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'config' => [
            'sql' => 'blob NULL',
        ],
    ],
];
