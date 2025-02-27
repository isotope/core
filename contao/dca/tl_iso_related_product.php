<?php

use Contao\DC_Table;
use Isotope\Backend;
use Isotope\Backend\RelatedProduct\Callback;
use Isotope\Model\Product;
use Isotope\Model\RelatedCategory;

/*
 * Table tl_iso_related_product
 */
$GLOBALS['TL_DCA']['tl_iso_related_product'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ptable' => 'tl_iso_product',
        'onload_callback' => [
            [Callback::class, 'initDCA'],
        ],
        'onsubmit_callback' => [
            [Backend::class, 'truncateProductCache'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['category'],
            'flag' => 1,
            'panelLayout' => 'filter,limit',
            'headerFields' => ['type', 'name', 'alias', 'sku'],
            'disableGrouping' => true,
            'child_record_callback' => [Callback::class, 'listRows'],
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\')) return false; Backend.getScrollOffset();"',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{category_legend},category;{products_legend},products',
    ],

    // Fields
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
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => RelatedCategory::getTable().'.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['table' => RelatedCategory::getTable(), 'type' => 'hasOne', 'load' => 'lazy'],
        ],
        'products' => [
            'exclude' => true,
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
