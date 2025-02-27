<?php

use Contao\DC_Table;
use Isotope\Backend\Download\Callback;

/*
 * Table tl_iso_download
 */
$GLOBALS['TL_DCA']['tl_iso_download'] = [
    // Config
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

    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['sorting'],
            'flag' => 1,
            'panelLayout' => 'filter;search,limit',
            'headerFields' => [],
            'header_callback' => [Callback::class, 'headerFields'],
            'child_record_callback' => [Callback::class, 'listRows'],
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['title', 'singleSRC'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
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
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
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
                'button_callback' => [Callback::class, 'deleteButton'],
            ],
            'toggle' => [
                'icon' => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback' => [Callback::class, 'toggleIcon'],
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{file_legend},singleSRC;{limit_legend},downloads_allowed,expires;{publish_legend},published',
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
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'singleSRC' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'files' => true, 'extensions' => $GLOBALS['TL_CONFIG']['allowedDownload']],
            'sql' => 'binary(16) NULL',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_files', 'field' => 'uuid'],
        ],
        'downloads_allowed' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 5, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(5) unsigned NOT NULL default '0'",
        ],
        'expires' => [
            'exclude' => true,
            'inputType' => 'timePeriod',
            'options' => ['minutes', 'hours', 'days', 'weeks', 'months', 'years'],
            'reference' => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'published' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
