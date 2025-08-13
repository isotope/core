<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend;
use Isotope\Backend\RelatedProduct\Callback;
use Isotope\Model\Product;
use Isotope\Model\RelatedCategory;

$GLOBALS['TL_DCA']['tl_iso_related_product'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'notSortable' => true,
        'ptable' => 'tl_iso_product',
        'onload_callback' => [
            [Callback::class, 'initDCA'],
        ],
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
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter,limit',
            'headerFields' => ['type', 'name', 'alias', 'sku'],
            'disableGrouping' => true,
            'child_record_callback' => [Callback::class, 'listRows'],
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
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'category' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => RelatedCategory::getTable().'.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['table' => RelatedCategory::getTable(), 'type' => 'hasOne', 'load' => 'lazy'],
        ],
        'products' => [
            'inputType' => 'picker',
            'eval' => ['mandatory' => true, 'multiple' => true, 'csv' => ',', 'tl_class' => 'clr', 'orderField' => 'productsOrder'],
            'sql' => 'blob NULL',
            'relation' => ['table' => Product::getTable(), 'type' => 'hasMany', 'load' => 'lazy'],
        ],
        'productsOrder' => [
            'sql' => 'blob NULL',
            'eval' => ['doNotShow' => true],
        ],
    ],
];
