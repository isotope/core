<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend;
use Isotope\Backend\ProductType\AttributeWizard;
use Isotope\Backend\ProductType\Callback;
use Isotope\Backend\ProductType\Label;
use Isotope\Backend\ProductType\Permission;
use Isotope\Model\Gallery;
use Isotope\Model\Product;

$GLOBALS['TL_DCA']['tl_iso_producttype'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'onload_callback' => [
            [Callback::class, 'checkPermission'],
            [Permission::class, 'check'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'variants' => 'index',
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
            'fields' => ['name', 'variants', 'downloads', 'shipping_exempt'],
            'showColumns' => true,
            'label_callback' => [Label::class, 'generate'],
        ],
    ],
    'palettes' => [
        '__selector__' => ['class', 'prices', 'variants'],
        'default' => '{name_legend},name,class',
        'standard' => '{name_legend},name,class,fallback;{description_legend:hide},description;{prices_legend:hide},prices;{template_legend},list_template,reader_template,list_gallery,reader_gallery,cssClass;{attributes_legend},attributes;{variants_legend:hide},variants;{expert_legend:hide},shipping_exempt,downloads',
    ],
    'subpalettes' => [
        'prices' => 'show_price_tiers',
        'variants' => 'variant_attributes,force_variant_options',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'class' => [
            'filter' => true,
            'inputType' => 'select',
            'default' => 'standard',
            'options_callback' => static fn () => Product::getModelTypeOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_product'],
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'fallback' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['fallback' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'description' => [
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:80px', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'prices' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'show_price_tiers' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'list_template' => [
            'inputType' => 'select',
            'default' => 'iso_list_default',
            'options_callback' => static fn () => Backend::getTemplates('iso_list_'),
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'reader_template' => [
            'inputType' => 'select',
            'default' => 'iso_reader_default',
            'options_callback' => static fn () => Backend::getTemplates('iso_reader_'),
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'list_gallery' => [
            'inputType' => 'select',
            'foreignKey' => Gallery::getTable().'.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'reader_gallery' => [
            'inputType' => 'select',
            'foreignKey' => Gallery::getTable().'.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'cssClass' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
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
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'tl_class' => 'clr',
                'columnsCallback' => [AttributeWizard::class, 'getColumns'],
                'buttons' => ['copy' => false, 'delete' => false],
                'dragAndDrop' => true,
            ],
            'sql' => 'blob NULL',
            'load_callback' => [
                [AttributeWizard::class, 'load'],
            ],
            'save_callback' => [
                [AttributeWizard::class, 'save'],
            ],
        ],
        'variants' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'variant_attributes' => [
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'tl_class' => 'clr',
                'columnsCallback' => [AttributeWizard::class, 'getColumns'],
                'buttons' => ['copy' => false, 'delete' => false],
                'dragAndDrop' => true,
            ],
            'sql' => 'blob NULL',
            'load_callback' => [
                [AttributeWizard::class, 'load'],
            ],
            'save_callback' => [
                [Callback::class, 'validateVariantAttributes'],
                [Callback::class, 'validateSingularAttributes'],
                [AttributeWizard::class, 'save'],
            ],
        ],
        'force_variant_options' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
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
            'sql' => "char(1) NOT NULL default ''",
        ],
        'downloads' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
