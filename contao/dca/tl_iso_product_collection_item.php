<?php

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_product_collection_item'] = [
    'config' => [
        'ptable' => 'tl_iso_product_collection',
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'identifier' => 'index',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product_collection.document_number',
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'sorting' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'type' => [
            'sql' => ['type' => Types::STRING, 'length' => 32],
        ],
        'identifier' => [
            'sql' => ['type' => Types::STRING, 'length' => 64, 'notnull' => false],
        ],
        'configuration' => [
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'sku' => [
            'sql' => ['type' => Types::STRING, 'length' => 128, 'notnull' => false],
        ],
        'label' => [
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'quantity' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'level' => [
            'sql' =>  ['type' => Types::SMALLINT, 'unsigned' => true, 'default' => 0],
        ],
        'unit_label' => [
            'sql' => ['type' => Types::STRING, 'length' => 128, 'notnull' => false],
        ],
        'price' => [
            'sql' => ['type' => Types::DECIMAL, 'precision' => 12, 'scale' => 2, 'default' => '0.00'],
        ],
        'tax_free_price' => [
            'sql' => ['type' => Types::DECIMAL, 'precision' => 12, 'scale' => 2, 'default' => '0.00'],
        ],
        'price_data' => [
            'sql' =>  ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_MEDIUMBLOB, 'notnull' => false],
        ],
        'tax_id' => [
            // Not the ID of a tax class or rate, this is the CSV list of applicable taxes (incremental numeric)
            'sql' => ['type' => Types::STRING, 'length' => 32, 'default' => ''],
        ],
        'referencePage' => [
            'foreignKey' => 'tl_page.title',
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'notnull' => false],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
    ],
];
