<?php

use Contao\BackendUser;
use Contao\DC_Table;
use Contao\System;

/*
 * Table tl_iso_label
 */
$GLOBALS['TL_DCA']['tl_iso_label'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'language' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['language', 'label'],
            'flag' => 1,
            'panelLayout' => 'filter,search,limit',
        ],
        'label' => [
            'fields' => ['language', 'label', 'replacement'],
            'showColumns' => true,
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
        'default' => '{label_legend},language,label,replacement',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'language' => [
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => System::getContainer()->get('contao.intl.locales')->getLocales(null, true),
            'default' => BackendUser::getInstance()->language,
            'eval' => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'label' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'replacement' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
