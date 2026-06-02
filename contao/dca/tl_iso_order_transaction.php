<?php

use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_order_transaction'] = [
    'config' => [
        'ptable' => 'tl_iso_product_collection',
        'ctable' => ['tl_iso_order_transaction_event'],
        'backendSearchIgnore' => true,
        'permissions' => [],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'uniqid' => 'unique',
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
            'foreignKey' => 'tl_iso_product_collection.document_number',
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'uniqid' => [
            'sql' => 'varchar(64) NOT NULL',
        ],
        'payment_id' => [
            'foreignKey' => 'tl_iso_payment.name',
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'created' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true],
        ],
        'completed' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'notnull' => false],
        ],
        'label' => [
            'sql' => 'text NULL',
        ],
        'amount' => [
            'sql' => 'text NULL',
        ],
        'currency' => [
            'sql' => 'text NULL',
        ],
        'status' => [
            'sql' => 'varchar(32) NULL',
        ],
    ],
];
