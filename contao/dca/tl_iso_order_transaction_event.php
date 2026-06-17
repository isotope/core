<?php

use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_order_transaction_event'] = [
    'config' => [
        'ptable' => 'tl_iso_order_transaction',
        'backendSearchIgnore' => true,
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
            'eval' => ['doNotShow' => true],
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'eval' => ['doNotShow' => true],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true],
        ],
        'tstamp' => [
            'eval' => ['doNotShow' => true, 'rgxp' => 'datim'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true],
        ],
        'payload' => [
            'eval' => ['doNotShow' => true],
            'sql' => ['type' => Types::JSON],
        ],
        'paypal_txn_id' => [
            'sql' => ['type' => Types::STRING, 'length' => 32, 'notnull' => false],
        ],
    ],
];
