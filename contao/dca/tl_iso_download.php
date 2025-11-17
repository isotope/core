<?php

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_iso_download'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ptable' => 'tl_iso_product',
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
            'fields' => ['sorting'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['name', 'alias', 'sku'],
        ],
        'label' => [
            'fields' => ['title', 'singleSRC'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ],
    ],
    'palettes' => [
        'default' => '{file_legend},singleSRC;{limit_legend},downloads_allowed,expires;{publish_legend},published',
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
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'singleSRC' => [
            'inputType' => 'fileTree',
            'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'files' => true, 'extensions' => $GLOBALS['TL_CONFIG']['allowedDownload']],
            'sql' => 'binary(16) NULL',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_files', 'field' => 'uuid'],
        ],
        'downloads_allowed' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 5, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(5) unsigned NOT NULL default '0'",
        ],
        'expires' => [
            'inputType' => 'timePeriod',
            'options' => ['minutes', 'hours', 'days', 'weeks', 'months', 'years'],
            'reference' => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'published' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
