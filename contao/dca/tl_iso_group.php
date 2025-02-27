<?php

use Contao\DC_Table;
use Contao\Input;
use Isotope\Backend\Group\Callback;
use Isotope\CompatibilityHelper;
use Isotope\Model\ProductType;

/*
 * Table tl_iso_group
 */
$GLOBALS['TL_DCA']['tl_iso_group'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'label' => &$GLOBALS['TL_LANG']['tl_iso_group']['label'],
        'backlink' => Input::get('popup') ? null : 'do=iso_products',
        'enableVersioning' => true,
        'onload_callback' => [
            [Callback::class, 'checkPermission'],
        ],
        'ondelete_callback' => [
            [Callback::class, 'deleteGroup'],
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
            'mode' => 5,
            'fields' => ['sorting'],
            'flag' => 1,
            'icon' => CompatibilityHelper::asset('images/folders.png'),
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
            'label_callback' => [Callback::class, 'addIcon'],
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
                'href' => 'table=tl_iso_group&amp;act=edit',
                'icon' => 'edit.svg',
                'button_callback' => [Callback::class, 'editButton'],
            ],
            'copy' => [
                'href' => 'table=tl_iso_group&amp;act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
                'button_callback' => [Callback::class, 'copyButton'],
            ],
            'copyChilds' => [
                'href' => 'table=tl_iso_group&amp;act=paste&amp;mode=copy&amp;childs=1',
                'icon' => 'copychilds.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
                'button_callback' => [Callback::class, 'copyButton'],
            ],
            'cut' => [
                'href' => 'table=tl_iso_group&amp;act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'delete' => [
                'href' => 'table=tl_iso_group&amp;act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [Callback::class, 'deleteButton'],
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{group_legend},name,product_type;',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_group.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'product_type' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => ProductType::getTable().'.name',
            'options_callback' => [Isotope\Backend\ProductType\Callback::class, 'getOptions'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
    ],
];
