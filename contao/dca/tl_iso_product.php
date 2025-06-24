<?php

use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Isotope\Backend;
use Isotope\Backend\Product\Alias;
use Isotope\Backend\Product\Button;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\DcaManager;
use Isotope\Backend\Product\Fallback;
use Isotope\Backend\Product\Panel;
use Isotope\Backend\Product\Permission;
use Isotope\Backend\Product\Price as BackendPrice;
use Isotope\Backend\Product\Wizard;
use Isotope\Backend\ProductPrice\Callback as ProductPriceCallback;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\DataContainer\ProductData;
use Isotope\Model\Attribute\BasePrice;
use Isotope\Model\Attribute\Price;
use Isotope\Model\Attribute\PriceTiers;
use Isotope\Model\Attribute\ShippingPrice;
use Isotope\Model\Attribute\Weight;
use Isotope\Model\AttributeOption;
use Isotope\Model\Download;
use Isotope\Model\ProductCategory;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;
use Isotope\Model\RelatedProduct;
use Isotope\Model\TaxClass;

System::loadLanguageFile(ProductType::getTable());

$GLOBALS['TL_DCA']['tl_iso_product'] = [
    'config' => [
        'label' => &$GLOBALS['TL_LANG']['MOD']['iso_products'][0],
        'dataContainer' => ProductData::class,
        'enableVersioning' => true,
        'switchToEdit' => true,
        'ptable' => 'tl_iso_product',
        'ctable' => [Download::getTable(), ProductCategory::getTable(), ProductPrice::getTable(), AttributeOption::getTable()],
        'onload_callback' => [
            [Permission::class, 'check'],
        ],
        'oncopy_callback' => [
            [Category::class, 'updateSorting'],
        ],
        'onsubmit_callback' => [
            [Backend::class, 'truncateProductCache'],
        ],
        'onversion_callback' => [
            [Category::class, 'createVersion'],
            // price version callbacks are added in the onload_callback (buildPaletteString)
        ],
        'onrestore_callback' => [
            [Category::class, 'restoreVersion'],
            // price version callbacks are added in the onload_callback (buildPaletteString)
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,language' => 'index',
                'language,fallback' => 'index',
                'language,published,start,stop,pid' => 'index',
                'start' => 'index',
                'sku' => 'index',
                'gtin' => 'index',
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
            'panel_callback' => [
                'iso_sorting' => [Panel::class, 'generateSortingIcon'],
            ],
        ],
        'label' => [
            'fields' => ['images', 'name', 'sku', 'price'],
            'showColumns' => true,
        ],
        'operations' => [
            'edit',
            'copy' => [
                'href' => 'act=copy&amp;childs=1',
                'icon' => 'copy.svg',
                'button_callback' => [Button::class, 'forCopy'],
            ],
            'cut',
            'delete',
            'toggle' => [
                'icon' => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback' => [Button::class, 'forVisibilityToggle'],
            ],
            'show',
            'break' => [
                'button_callback' => static fn () => '<br>',
            ],
            'fallback' => [
                'href' => 'key=fallback',
                'icon' => 'featured.svg',
                'button_callback' => [Button::class, 'forFallbackToggle'],
            ],
            'variants' => [
                'href' => '',
                'icon' => CompatibilityHelper::asset('images/table--pencil.png'),
                'button_callback' => [Button::class, 'forVariants'],
            ],
            'related' => [
                'href' => 'table='.RelatedProduct::getTable(),
                'icon' => CompatibilityHelper::asset('images/sitemap.png'),
                'button_callback' => [Button::class, 'forRelated'],
            ],
            'downloads' => [
                'href' => 'table='.Download::getTable(),
                'icon' => CompatibilityHelper::asset('images/paper-clip.png'),
                'button_callback' => [Button::class, 'forDownloads'],
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
            // Fix for DC_Table, otherwise getPalette() will not use the PID value
            'eval' => ['submitOnChange' => true],
            'attributes' => ['systemColumn' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sorting' => true,
            'attributes' => ['systemColumn' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
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
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'hitCount' => [
            'sorting' => true,
            'flag' => DataContainer::SORT_DESC,
            'attributes' => ['fe_sorting' => true, 'systemColumn' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => ProductType::getTable().'.name',
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 wizard', 'helpwizard' => true],
            'attributes' => ['legend' => 'general_legend', 'fixed' => true, 'inherit' => true, 'systemColumn' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'explanation' => 'tl_iso_product.type',
            'wizard' => [[Wizard::class, 'onProductTypeWizard']],
        ],
        'pages' => [
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['doNotSaveEmpty' => true, 'multiple' => true, 'fieldType' => 'checkbox', 'orderField' => 'orderPages', 'tl_class' => 'clr hide_sort_hint'],
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
            'attributes' => ['legend' => 'general_legend', 'fixed' => true, 'inherit' => true, 'systemColumn' => true],
            'load_callback' => [
                [Category::class, 'load'],
            ],
            'save_callback' => [
                [Category::class, 'save'],
            ],
        ],
        'orderPages' => [
            'eval' => ['doNotShow' => true],
            'attributes' => ['systemColumn' => true, 'inherit' => true],
            'sql' => 'text NULL',
        ],
        'inherit' => [
            'inputType' => 'inheritCheckbox',
            'eval' => ['multiple' => true],
            'attributes' => ['systemColumn' => true],
            'sql' => 'blob NULL',
        ],
        'fallback' => [
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'variant_fixed' => true, 'excluded' => true, 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
            'save_callback' => [
                [Fallback::class, 'reset'],
            ],
        ],
        'alias' => [
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alnum', 'doNotCopy' => true, 'spaceToUnderscore' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'general_legend', 'fixed' => true, 'inherit' => true],
            'sql' => "varchar(128) NOT NULL default ''",
            'save_callback' => [
                [Alias::class, 'save'],
            ],
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
            'eval' => ['mandatory' => true, 'tl_class' => 'clr long'],
            'attributes' => ['legend' => 'general_legend', 'multilingual' => true, 'fixed' => true, 'fe_sorting' => true, 'fe_search' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'teaser' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:80px', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'multilingual' => true, 'fe_search' => true],
            'sql' => 'text NULL',
        ],
        'description' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'multilingual' => true, 'fe_search' => true],
            'sql' => 'text NULL',
        ],
        'meta_title' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
            'attributes' => ['legend' => 'meta_legend', 'multilingual' => true, 'variant_excluded' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'meta_description' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:60px', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'meta_legend', 'multilingual' => true, 'variant_excluded' => true],
            'sql' => 'text NULL',
        ],
        'meta_keywords' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:40px', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'meta_legend', 'multilingual' => true, 'variant_excluded' => true],
            'sql' => 'text NULL',
        ],
        'price' => [
            'inputType' => 'timePeriod',
            'foreignKey' => TaxClass::getTable().'.name',
            'eval' => ['mandatory' => true, 'maxlength' => 13, 'rgxp' => 'digit', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'doNotSaveEmpty' => true, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'pricing_legend', 'fe_sorting' => true, 'dynamic' => true, 'singular' => true, 'systemColumn' => true, 'type' => Price::class],
            'load_callback' => [
                [BackendPrice::class, 'load'],
            ],
            'save_callback' => [
                [BackendPrice::class, 'save'],
            ],
        ],
        'prices' => [
            'inputType' => 'dcaWizard',
            'foreignTable' => ProductPrice::getTable(),
            'attributes' => ['systemColumn' => true],
            'eval' => [
                'listCallback' => [ProductPriceCallback::class, 'generateWizardList'],
                'list_callback' => [ProductPriceCallback::class, 'generateWizardList'],
                'applyButtonLabel' => &$GLOBALS['TL_LANG']['tl_iso_product']['prices']['apply_and_close'],
                'global_operations' => ['new'],
                'operations' => ['edit', 'copy', 'delete', 'show'],
                'tl_class' => 'clr',
            ],
        ],
        'price_tiers' => [
            // This is only for automated table generation in the frontend
            'attributes' => ['type' => PriceTiers::class],
            'tableformat' => [
                'min' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['min'],
                    'format' => &$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['min_format'],
                ],
                'price' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['price'],
                    'rgxp' => 'digit',
                ],
                'tax_class' => [
                    'doNotShow' => true,
                ],
            ],
        ],
        'baseprice' => [
            'inputType' => 'timePeriod',
            'foreignKey' => 'tl_iso_baseprice.name',
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'attributes' => ['type' => BasePrice::class, 'legend' => 'pricing_legend'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'shipping_weight' => [
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'attributes' => ['legend' => 'shipping_legend', 'type' => Weight::class],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'shipping_exempt' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend', 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'shipping_pickup' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend', 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'shipping_price' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend', 'systemColumn' => true, 'type' => ShippingPrice::class],
            'sql' => "decimal(9,2) unsigned NOT NULL default '0.00'",
        ],
        'images' => [
            'inputType' => 'mediaManager',
            'explanation' => 'mediaManager',
            'eval' => ['extensions' => '%contao.image.valid_extensions%', 'helpwizard' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'media_legend', 'fixed' => true, 'multilingual' => true, 'dynamic' => true, 'systemColumn' => true, 'fetch_fallback' => true],
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
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'publish_legend', 'fixed' => true, 'variant_fixed' => true, 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
            'save_callback' => [
                [Backend::class, 'truncateProductCache'],
            ],
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
    $GLOBALS['TL_DCA']['tl_iso_product']['config']['switchToEdit'] = false;
} else {
    $GLOBALS['TL_DCA']['tl_iso_product']['config']['notSortable'] = true;
}
