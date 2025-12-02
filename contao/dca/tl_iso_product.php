<?php

use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Isotope\Backend\Product\Permission;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\DataContainer\ProductData;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Model\RelatedProduct;
use Isotope\Model\Download;
use Isotope\Model\TaxClass;

System::loadLanguageFile(ProductType::getTable());

$GLOBALS['TL_DCA']['tl_iso_product'] = [
    'config' => [
        'label' => &$GLOBALS['TL_LANG']['MOD']['iso_products'][0],
        'dataContainer' => ProductData::class,
        'enableVersioning' => true,
        'ctable' => ['tl_iso_product', 'tl_iso_download', 'tl_iso_attribute_option'],
        'onload_callback' => [
            [Permission::class, 'check'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,language' => 'index',
                'language,fallback' => 'index',
                'language,published,start,stop,pid' => 'index',
                'start' => 'index',
                'sku' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['name'],
            'headerFields' => ['name', 'sku', 'price', 'published'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;sort,iso_sorting,search,limit',
        ],
        'label' => [
            'fields' => ['images', 'name', 'sku', 'price'],
            'showColumns' => true,
        ],
        'operations' => [
            'edit',
            'children' => [
                // needs to be defined, otherwise Contao will generate children for the first ctable
                'href' => 'table=tl_iso_product',
                'icon' => 'children.svg',
                'primary' => true,
            ],
            'copy',
            'cut',
            'delete',
            'toggle' => [
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
                'showInHeader' => true,
                'primary' => true,
            ],
            'fallback' => [
                'href' => 'act=toggle&amp;field=fallback',
                'icon' => 'featured.svg',
                'primary' => true,
            ],
            'show',
            '-',
            'related' => [
                'href' => 'table='.RelatedProduct::getTable(),
                'icon' => CompatibilityHelper::asset('images/related.svg'),
            ],
            'downloads' => [
                'href' => 'table='.Download::getTable(),
                'icon' => CompatibilityHelper::asset('images/paper-clip.png'),
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'protected'],
        'default' => '{general_legend},type',
    ],
    'subpalettes' => [
        'protected' => 'groups',
    ],
    'fields' => [
        'id' => [
            'attributes' => ['systemColumn' => true],
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'attributes' => ['systemColumn' => true],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'tstamp' => [
            'sorting' => true,
            'attributes' => ['systemColumn' => true],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'language' => [
            'eval' => ['doNotShow' => true],
            'attributes' => ['systemColumn' => true, 'inherit' => true],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'dateAdded' => [
            'default' => static fn () => time(),
            'sorting' => true,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'attributes' => ['fe_sorting' => true, 'systemColumn' => true],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'hitCount' => [
            'sorting' => true,
            'flag' => DataContainer::SORT_DESC,
            'attributes' => ['fe_sorting' => true, 'systemColumn' => true],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_producttype.name',
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 wizard', 'helpwizard' => true],
            'attributes' => ['legend' => 'general_legend', 'fixed' => true, 'inherit' => true, 'systemColumn' => true],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'explanation' => 'tl_iso_product.type',
        ],
        'pages' => [
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['doNotSaveEmpty' => true, 'multiple' => true, 'fieldType' => 'checkbox', 'isSortable' => true, 'tl_class' => 'clr hide_sort_hint'],
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
            'attributes' => ['legend' => 'general_legend', 'fixed' => true, 'inherit' => true, 'systemColumn' => true],
        ],
        'inherit' => [
            'inputType' => 'inheritCheckbox',
            'eval' => ['multiple' => true, 'translatableFor' => '*'],
            'attributes' => ['systemColumn' => true],
            'sql' => 'blob NULL',
        ],
        'fallback' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'fallback' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'variant_fixed' => true, 'excluded' => true, 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'alias' => [
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alnum', 'doNotCopy' => true, 'spaceToUnderscore' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'general_legend', 'fixed' => true, 'inherit' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'gtin' => [
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 14, 'unique' => true, 'doNotCopy' => true, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'general_legend', 'fe_search' => true, 'singular' => true],
            'sql' => "varchar(14) NOT NULL default ''",
        ],
        'sku' => [
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'unique' => true, 'doNotCopy' => true, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'general_legend', 'fe_sorting' => true, 'fe_search' => true, 'singular' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'name' => [
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'translatableFor' => '*', 'tl_class' => 'clr long'],
            'attributes' => ['legend' => 'general_legend', 'fixed' => true, 'fe_sorting' => true, 'fe_search' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'teaser' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['translatableFor' => '*', 'style' => 'height:80px', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'fe_search' => true],
            'sql' => 'text NULL',
        ],
        'description' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'rte' => 'tinyMCE', 'translatableFor' => '*', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'fe_search' => true],
            'sql' => 'text NULL',
        ],
        'meta_title' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'translatableFor' => '*', 'tl_class' => 'clr long'],
            'attributes' => ['legend' => 'meta_legend', 'variant_excluded' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'meta_description' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['translatableFor' => '*', 'style' => 'height:60px', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'meta_legend', 'variant_excluded' => true],
            'sql' => 'text NULL',
        ],
        'price' => [
            'inputType' => 'timePeriod',
            'foreignKey' => TaxClass::getTable().'.name',
            'eval' => ['mandatory' => true, 'maxlength' => 13, 'rgxp' => 'digit', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'doNotSaveEmpty' => true, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'pricing_legend', 'fe_sorting' => true, 'dynamic' => true, 'singular' => true, 'systemColumn' => true],
            'sql' => 'text NULL',
        ],
        'prices' => [
            'inputType' => 'rowWizard',
            'exclude' => false,
            'fields' => [
                'price_tiers' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['prices']['price_tiers'],
                    'inputType' => 'rowWizard',
                    'fields' => [
                        'min' => [
                            'label' => '',
                            'default' => 1,
                            'inputType' => 'text',
                            'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'style' => 'width:50px'],
                        ],
                        'price' => [
                            'label' => '',
                            'inputType' => 'text',
                            'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'style' => 'width:120px'],
                        ],
                    ],
                    'eval' => ['mandatory' => true, 'cell_style' => 'width:220px', 'sortable' => false],
                ],
                'tax_class' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['prices']['tax_class'],
                    'default' => static fn () => TaxClass::findFallback()?->id,
                    'inputType' => 'select',
                    'foreignKey' => TaxClass::getTable().'.name',
                    'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class' => 'clr'],
                ],
                'config_id' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['prices']['config_id'],
                    'inputType' => 'select',
                    'foreignKey' => 'tl_iso_config.name',
                    'eval' => ['includeBlankOption' => true],
                ],
                'member_group' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['prices']['member_group'],
                    'inputType' => 'select',
                    'foreignKey' => 'tl_member_group.name',
                    'eval' => ['includeBlankOption' => true, 'chosen' => true],
                ],
                'start' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['prices']['start'],
                    'inputType' => 'text',
                    'eval' => ['rgxp' => 'date', 'datepicker' => true, 'cell_style' => 'width:150px'],
                ],
                'stop' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['prices']['stop'],
                    'inputType' => 'text',
                    'eval' => ['rgxp' => 'date', 'datepicker' => true, 'cell_style' => 'width:150px'],
                ],
            ],
            'eval' => ['tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'baseprice' => [
            'inputType' => 'timePeriod',
            'foreignKey' => 'tl_iso_baseprice.name',
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'pricing_legend'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'shipping_weight' => [
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'attributes' => ['legend' => 'shipping_legend'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'shipping_exempt' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'shipping_pickup' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'shipping_price' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend'],
            'sql' => "decimal(9,2) unsigned NOT NULL default '0.00'",
        ],
        'images' => [
            'inputType' => 'mediaManager',
            'explanation' => 'mediaManager',
            'eval' => ['translatableFor' => '*', 'extensions' => '%contao.image.valid_extensions%', 'helpwizard' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'media_legend', 'fixed' => true, 'dynamic' => true, 'systemColumn' => true, 'fetch_fallback' => true],
            'sql' => 'blob NULL',
        ],
        'protected' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'expert_legend', 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true, 'systemColumn' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'cssID' => [
            'inputType' => 'text',
            'eval' => ['multiple' => true, 'size' => 2, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'expert_legend'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'toggle' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'publish_legend', 'fixed' => true, 'variant_fixed' => true, 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'start' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'attributes' => ['legend' => 'publish_legend', 'fixed' => true, 'variant_fixed' => true, 'systemColumn' => true],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'attributes' => ['legend' => 'publish_legend', 'fixed' => true, 'variant_fixed' => true, 'systemColumn' => true],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'variantFields' => [
            'label' => &$GLOBALS['TL_LANG'][ProductType::getTable()]['variant_attributes'],
        ],
        'source' => [
            'eval' => ['mandatory' => true, 'required' => true, 'fieldType' => 'radio'],
        ],
    ],
];

/*
 * Adjust the data configuration array in variants view
 */
if (Input::get('id')) {
    $GLOBALS['TL_LANG']['tl_iso_product']['new'] = &$GLOBALS['TL_LANG']['tl_iso_product']['new_variant'];
}
