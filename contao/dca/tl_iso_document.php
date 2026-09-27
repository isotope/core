<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_document'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'defaultSearchField' => 'name',
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'search,filter,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
        ],
    ],
    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{type_legend},name,type',
        'standard' => '{type_legend},name,type;{config_legend},documentTitle,fileTitle,numberPrefix,numberDigits;{template_legend},customTpl,gallery',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'default' => 'standard',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_document'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'documentTitle' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'fileTitle' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'numberPrefix' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'numberDigits' => [
            'default' => 4,
            'inputType' => 'select',
            'options' => range(1, 9),
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 4],
        ],
        'customTpl' => [
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'gallery' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_gallery.name',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
    ],
];
