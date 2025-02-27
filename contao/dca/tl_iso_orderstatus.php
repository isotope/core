<?php

use Contao\DC_Table;
use Isotope\Backend\OrderStatus\Callback;
use Isotope\CompatibilityHelper;

/*
 * Table tl_iso_orderstatus
 */
$GLOBALS['TL_DCA']['tl_iso_orderstatus'] = [
    // Config
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

    // List
    'list' => [
        'sorting' => [
            'mode' => 5,
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
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
            ],
            'cut' => [
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
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
        'default' => '{name_legend},name,color,paid,welcomescreen;{email_legend},notification;{payment_legend:hide},saferpay_status',
    ],

    // Fields
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
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'color' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['minlength' => 6, 'maxlength' => 6, 'colorpicker' => true, 'isHexColor' => true, 'decodeEntities' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(6) NOT NULL default ''",
        ],
        'paid' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'welcomescreen' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'notification' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getNotificationChoices'],
            'eval' => ['multiple' => true, 'csv' => ',', 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'saferpay_status' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['capture', 'cancel'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['saferpay_status'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
    ],
];
