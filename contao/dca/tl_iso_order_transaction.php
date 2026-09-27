<?php

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_order_transaction'] = [
    'config' => [
        'ptable' => 'tl_iso_product_collection',
        'ctable' => ['tl_iso_order_transaction_event'],
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'uniqid' => 'unique',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product_collection.document_number',
            'sql' =>  ['type' => Types::INTEGER, 'unsigned' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'uniqid' => [
            'sql' => ['type' => Types::STRING, 'length' => 64],
        ],
        'payment_id' => [
            'foreignKey' => 'tl_iso_payment.name',
            'sql' =>  ['type' => Types::INTEGER, 'unsigned' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'created' => [
            'sql' =>  ['type' => Types::INTEGER, 'unsigned' => true],
        ],
        'completed' => [
            'sql' =>  ['type' => Types::INTEGER, 'unsigned' => true, 'notnull' => false],
        ],
        'label' => [
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'amount' => [
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'currency' => [
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'status' => [
            'sql' => ['type' => Types::STRING, 'length' => 32, 'notnull' => false],
        ],
    ],
];
