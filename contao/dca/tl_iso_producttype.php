<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Contao\System;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_producttype'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
                'variants' => 'index',
            ],
        ],
        'userRoot' => 'iso_producttypes',
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'variants', 'prices', 'shipping_exempt'],
            'showColumns' => true,
        ],
    ],
    'palettes' => [
        '__selector__' => ['class', 'prices', 'variants'],
        'default' => '{name_legend},name,class',
        'standard' => '{name_legend},name,class,fallback;{description_legend:hide},description;{prices_legend:hide},prices;{template_legend},list_template,reader_template,list_gallery,reader_gallery,cssClass;{attributes_legend},attributes;{variants_legend:hide},variants;{expert_legend:hide},shipping_exempt',
    ],
    'subpalettes' => [
        'prices' => 'show_price_tiers',
        'variants' => 'variant_attributes,force_variant_options',
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
        'class' => [
            'filter' => true,
            'inputType' => 'select',
            'default' => 'standard',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_product'],
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'fallback' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['fallback' => true, 'tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'description' => [
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:80px', 'tl_class' => 'clr'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'prices' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'show_price_tiers' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'list_template' => [
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => 'isotope/product/list'],
        ],
        'reader_template' => [
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => 'isotope/product/reader'],
        ],
        'list_gallery' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_gallery.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'reader_gallery' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_gallery.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'cssClass' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'attributes' => [
            'default' => [
                ['name' => 'type', 'enabled' => 1],
                ['name' => 'pages', 'enabled' => 1],
                ['name' => 'alias', 'enabled' => 1],
                ['name' => 'sku', 'enabled' => 1],
                ['name' => 'name', 'enabled' => 1],
                ['name' => 'teaser', 'enabled' => 1],
                ['name' => 'description', 'enabled' => 1],
                ['name' => 'price', 'enabled' => 1],
                ['name' => 'images', 'enabled' => 1],
                ['name' => 'published', 'enabled' => 1],
                ['name' => 'start', 'enabled' => 1],
                ['name' => 'stop', 'enabled' => 1],
            ],
            'inputType' => 'rowWizard',
            'eval' => [
                'tl_class' => 'clr',
                'actions' => [],
            ],
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'variants' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr', 'submitOnChange' => true],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'variant_attributes' => [
            'inputType' => 'rowWizard',
            'eval' => [
                'tl_class' => 'clr',
                'actions' => [],
            ],
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'force_variant_options' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'validAliasCharacters' => [
            'inputType' => 'select',
            'options_callback' => static fn () => System::getContainer()->get('contao.slug.valid_characters')->getOptions(),
            'eval' => ['includeBlankOption' => true, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'shipping_exempt' => [
            'filter' => true,
            'inputType' => 'select',
            'explanation' => 'shippingExempt',
            'options' => [
                1 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['shipping_exempt']['exempt'],
                2 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['shipping_exempt']['pickup'],
            ],
            'eval' => [
                'tl_class' => 'w50',
                'includeBlankOption' => true,
                'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_iso_producttype']['shipping_exempt']['default'],
                'helpwizard' => true,
            ],
            'sql' => ['type' => Types::STRING, 'length' => 1, 'fixed' => true, 'default' => ''],
        ],
    ],
];
