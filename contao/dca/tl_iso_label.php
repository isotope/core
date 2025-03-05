<?php

use Contao\BackendUser;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;

$GLOBALS['TL_DCA']['tl_iso_label'] = [
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
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['language', 'label'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter,search,limit',
        ],
        'label' => [
            'fields' => ['language', 'label', 'replacement'],
            'showColumns' => true,
        ],
    ],
    'palettes' => [
        'default' => '{label_legend},language,label,replacement',
    ],
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
