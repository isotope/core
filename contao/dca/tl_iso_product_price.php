<?php

use Contao\DC_Table;
use Contao\MemberGroupModel;
use Isotope\Backend\ProductPrice\Callback;
use Isotope\Model\Config;
use Isotope\Model\TaxClass;

/*
 * Table tl_iso_product_price
 */
$GLOBALS['TL_DCA']['tl_iso_product_price'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ptable' => 'tl_iso_product',
        'ctable' => ['tl_iso_product_pricetier'],
        'onload_callback' => [
            [Callback::class, 'initializeDCA'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'config_id,member_group,start,stop,pid' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['id'],
            'flag' => 1,
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['id', 'name', 'alias', 'sku'],
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
        'default' => '{price_legend},price_tiers,tax_class;{limit_legend},config_id,member_group,start,stop',
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
        'price_tiers' => [
            'exclude' => true,
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'doNotSaveEmpty' => true,
                'tl_class' => 'clr',
                'disableSorting' => true,
                'columnFields' => [
                    'min' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_iso_product_price']['price_tier_columns']['min'],
                        'inputType' => 'text',
                        'eval' => ['mandatory' => true, 'rgxp' => 'digit'],
                    ],
                    'price' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_iso_product_price']['price_tier_columns']['price'],
                        'inputType' => 'text',
                        'eval' => ['mandatory' => true, 'rgxp' => 'digit'],
                    ],
                ],
            ],
            'load_callback' => [
                [Callback::class, 'loadTiers'],
            ],
            'save_callback' => [
                [Callback::class, 'saveTiers'],
            ],
        ],
        'tax_class' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => TaxClass::getTable().'.name',
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class' => 'clr'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'config_id' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => Config::getTable().'.name',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'member_group' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => MemberGroupModel::getTable().'.name',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'start' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
