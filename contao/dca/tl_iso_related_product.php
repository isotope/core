<?php

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_iso_related_product'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ptable' => 'tl_iso_product',
        'notSortable' => true,
        'backendSearchIgnore' => true,
        'sql' => [
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
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'category' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_related_category.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'doNotCopy' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'products' => [
            'filter' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_iso_product.name',
            'eval' => ['mandatory' => true, 'multiple' => true, 'csv' => ',', 'tl_class' => 'clr', 'isSortable' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
    ],
];
