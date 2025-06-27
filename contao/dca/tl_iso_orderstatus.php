<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend\OrderStatus\Callback;

$GLOBALS['TL_DCA']['tl_iso_orderstatus'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'paid' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'color', 'paid'],
            'showColumns' => true,
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,color,paid,welcomescreen;{email_legend},notification',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
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
    ],
];
