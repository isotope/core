<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_related_product'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ptable' => 'tl_iso_product',
        'notSortable' => true,
        'backendSearchIgnore' => true,
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['category'],
            'panelLayout' => 'filter,limit',
            'headerFields' => ['type', 'name', 'alias', 'sku', 'published'],
            'renderAsGrid' => true,
            'limitHeight' => 160,
        ],
        'label' => [
            'fields' => ['category'],
            'format' => '%s',
        ],
    ],
    'palettes' => [
        'default' => '{category_legend},category;{products_legend},products',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product.name',
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'category' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_related_category.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'doNotCopy' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'products' => [
            'filter' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_iso_product.name',
            'eval' => ['mandatory' => true, 'multiple' => true, 'csv' => ',', 'tl_class' => 'clr', 'isSortable' => true],
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
    ],
];
