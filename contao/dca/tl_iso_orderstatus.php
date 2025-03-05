<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend\OrderStatus\Callback;
use Isotope\CompatibilityHelper;

$GLOBALS['TL_DCA']['tl_iso_orderstatus'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'label' => &$GLOBALS['TL_LANG']['IMD']['orderstatus'][0],
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'onload_callback' => [
            [Callback::class, 'addDefault'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'paid' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_TREE,
            'fields' => ['name'],
            'panelLayout' => 'filter;search,limit',
            'paste_button_callback' => [Callback::class, 'pasteButton'],
            'icon' => CompatibilityHelper::asset('images/traffic-light.png'),
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
            'label_callback' => [Callback::class, 'getColoredLabel'],
        ],
        'global_operations' => [
            'all',
        ],
        'operations' => [
            'edit',
            'copy',
            'cut',
            'delete',
            'show',
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,color,paid,welcomescreen;{email_legend},notification;{payment_legend:hide},saferpay_status',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'color' => [
            'inputType' => 'text',
            'eval' => ['minlength' => 6, 'maxlength' => 6, 'colorpicker' => true, 'isHexColor' => true, 'decodeEntities' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(6) NOT NULL default ''",
        ],
        'paid' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'welcomescreen' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'notification' => [
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getNotificationChoices'],
            'eval' => ['multiple' => true, 'csv' => ',', 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'saferpay_status' => [
            'inputType' => 'select',
            'options' => ['capture', 'cancel'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['saferpay_status'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
    ],
];
