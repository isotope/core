<?php

use Contao\DC_Table;
use Isotope\Backend;
use Isotope\Model\Document;
use Isotope\Model\Gallery;

/*
 * Table tl_iso_document
 */
$GLOBALS['TL_DCA']['tl_iso_document'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['name'],
            'flag' => 1,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
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
        '__selector__' => ['type'],
        'default' => '{type_legend},name,type',
        'standard' => '{type_legend},name,type;{config_legend},documentTitle,fileTitle;{template_legend},documentTpl,gallery,collectionTpl,orderCollectionBy',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
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
        'type' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'default' => 'standard',
            'options_callback' => static fn () => Document::getModelTypeOptions(),
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'documentTitle' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'fileTitle' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'documentTpl' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => Backend::getTemplates('iso_document_'),
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'collectionTpl' => [
            'exclude' => true,
            'default' => 'iso_collection_invoice',
            'inputType' => 'select',
            'options_callback' => static fn () => Backend::getTemplates('iso_collection_'),
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'orderCollectionBy' => [
            'exclude' => true,
            'default' => 'asc_id',
            'inputType' => 'select',
            'options' => &$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'gallery' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => Gallery::getTable().'.name',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];
