<?php

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_product_collection_item'] = [
    'config' => [
        'ptable' => 'tl_iso_product_collection',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'identifier' => 'index',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product_collection.document_number',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'sql' => 'varchar(32) NOT NULL',
        ],
        'identifier' => [
            'sql' => 'varchar(64) NOT NULL',
        ],
        'configuration' => [
            'sql' => 'blob NULL',
        ],
        'sku' => [
            'sql' => 'varchar(128) NULL',
        ],
        'label' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'quantity' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'level' => [
            'sql' => ['type' => Types::SMALLINT, 'unsigned' => true, 'default' => 0],
        ],
        'unit_label' => [
            'sql' => 'varchar(128) NULL',
        ],
        'price' => [
            'sql' => 'decimal(12,2) NOT NULL default 0.00',
        ],
        'tax_free_price' => [
            'sql' => 'decimal(12,2) NOT NULL default 0.00',
        ],
        'price_data' => [
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_MEDIUMBLOB, 'notnull' => false],
        ],
        'tax_id' => [
            // Not the ID of a tax class or rate, this is the CSV list of applicable taxes (incremental numeric)
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'referencePage' => [
            'foreignKey' => 'tl_page.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
    ],
];
