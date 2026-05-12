<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend;

$GLOBALS['TL_DCA']['tl_iso_document'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
        ],
    ],
    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{type_legend},name,type',
        'standard' => '{type_legend},name,type;{config_legend},documentTitle,fileTitle;{template_legend},documentTpl,gallery,collectionTpl,orderCollectionBy',
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
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'default' => 'standard',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_document'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'documentTitle' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'fileTitle' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'documentTpl' => [
            'inputType' => 'select',
            'options_callback' => static fn () => \Contao\Controller::getTemplateGroup('iso_document_'),
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'collectionTpl' => [
            'default' => 'iso_collection_invoice',
            'inputType' => 'select',
            'options_callback' => static fn () => \Contao\Controller::getTemplateGroup('iso_collection_'),
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'orderCollectionBy' => [
            'default' => 'asc_id',
            'inputType' => 'select',
            'options' => &$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'gallery' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_gallery.name',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];
