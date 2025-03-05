<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Isotope\Backend\Group\Callback;
use Isotope\CompatibilityHelper;
use Isotope\Model\ProductType;

$GLOBALS['TL_DCA']['tl_iso_group'] = [
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
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_TREE,
            'fields' => ['sorting'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'icon' => CompatibilityHelper::asset('images/folders.png'),
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
            'label_callback' => [Callback::class, 'addIcon'],
        ],
    ],
    'palettes' => [
        'default' => '{group_legend},name,product_type;',
    ],
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
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'product_type' => [
            'inputType' => 'select',
            'foreignKey' => ProductType::getTable().'.name',
            'options_callback' => [Isotope\Backend\ProductType\Callback::class, 'getOptions'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
    ],
];
