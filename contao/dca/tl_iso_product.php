<?php

use Contao\Input;
use Contao\System;
use Isotope\Backend;
use Isotope\Backend\Product\Alias;
use Isotope\Backend\Product\Button;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\DcaManager;
use Isotope\Backend\Product\Fallback;
use Isotope\Backend\Product\Label;
use Isotope\Backend\Product\Panel;
use Isotope\Backend\Product\Permission;
use Isotope\Backend\Product\Wizard;
use Isotope\Backend\Product\XmlSitemap;
use Isotope\Backend\ProductType\Callback;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\DataContainer\ProductData;
use Isotope\Model\Attribute\BasePrice;
use Isotope\Model\Attribute\Price;
use Isotope\Model\Attribute\PriceTiers;
use Isotope\Model\Attribute\ShippingPrice;
use Isotope\Model\Attribute\Weight;
use Isotope\Model\AttributeOption;
use Isotope\Model\Download;
use Isotope\Model\Group;
use Isotope\Model\ProductCategory;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;
use Isotope\Model\RelatedProduct;
use Isotope\Model\TaxClass;

System::loadLanguageFile(ProductType::getTable());

/*
 * Table tl_iso_product
 */
$GLOBALS['TL_DCA']['tl_iso_product'] = [
    // Config
    'config' => [
        'label' => &$GLOBALS['TL_LANG']['MOD']['iso_products'][0],
        'dataContainer' => ProductData::class,
        'enableVersioning' => true,
        'switchToEdit' => true,
        'ptable' => 'tl_iso_product',
        'ctable' => [Download::getTable(), ProductCategory::getTable(), ProductPrice::getTable(), AttributeOption::getTable()],
        'onload_callback' => [
            [DcaManager::class, 'load'],
            [Permission::class, 'check'],
            [Panel::class, 'applyAdvancedFilters'],
            [XmlSitemap::class, 'generate'],
        ],
        'oncreate_callback' => [
            [DcaManager::class, 'updateNewRecord'],
        ],
        'oncopy_callback' => [
            [Category::class, 'updateSorting'],
            [DcaManager::class, 'updateDateAdded'],
        ],
        'onsubmit_callback' => [
            [Backend::class, 'truncateProductCache'],
            [XmlSitemap::class, 'scheduleUpdate'],
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
                'gid' => 'index',
                'pid,language' => 'index',
                'language,fallback' => 'index',
                'language,published,start,stop,pid' => 'index',
                'start' => 'index',
                'sku' => 'index',
                'gtin' => 'index',
            ],
        ],
    ],

    // Select
    'select' => [
        'buttons_callback' => [
            [Button::class, 'forSelect'],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['name'],
            'headerFields' => ['name', 'sku', 'price', 'published'],
            'flag' => 1,
            'panelLayout' => 'iso_buttons,iso_filter;filter;sort,iso_sorting,search,limit',
            'panel_callback' => [
                'iso_buttons' => [Panel::class, 'generateFilterButtons'],
                'iso_filter' => [Panel::class, 'generateAdvancedFilters'],
                'iso_sorting' => [Panel::class, 'generateSortingIcon'],
            ],
        ],
        'label' => [
            'fields' => ['images', 'name', 'sku', 'price'],
            'showColumns' => true,
            'label_callback' => [Label::class, 'generate'],
        ],
        'global_operations' => [
            'generate' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['generate'],
                'href' => 'key=generate',
                'icon' => 'new.svg',
            ],
            'groups' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['product_groups'],
                'href' => 'table='.Group::getTable(),
                'icon' => CompatibilityHelper::asset('images/folders.png'),
                'attributes' => 'onclick="Backend.getScrollOffset();"',
                'button_callback' => [Button::class, 'forGroups'],
            ],
            'import' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['import'],
                'href' => 'key=import',
                'icon' => CompatibilityHelper::asset('images/image--plus.png'),
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
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
                'href' => 'act=copy&amp;childs=1',
                'icon' => 'copy.svg',
                'button_callback' => [Button::class, 'forCopy'],
            ],
            'cut' => [
                'href' => 'act=cut',
                'icon' => 'cut.svg',
                'button_callback' => [Button::class, 'forCut'],
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [Button::class, 'forDelete'],
            ],
            'toggle' => [
                'icon' => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback' => [Button::class, 'forVisibilityToggle'],
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
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
            'group' => [
                'href' => 'act=cut',
                'icon' => CompatibilityHelper::asset('images/folder-network.png'),
                'button_callback' => [Button::class, 'forGroup'],
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type', 'protected'],
        'default' => '{general_legend},type',
    ],

    // Subpalettes
    'subpalettes' => [
        'protected' => 'groups',
    ],

    // Fields
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
        'gid' => [
            'foreignKey' => Group::getTable().'.name',
            'eval' => ['doNotShow' => true],
            'attributes' => ['systemColumn' => true, 'inherit' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
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
            'sorting' => true,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'attributes' => ['fe_sorting' => true, 'systemColumn' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'hitCount' => [
            'sorting' => true,
            'flag' => 12,
            'attributes' => ['fe_sorting' => true, 'systemColumn' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getOptions'],
            'foreignKey' => ProductType::getTable().'.name',
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 wizard', 'helpwizard' => true],
            'attributes' => ['legend' => 'general_legend', 'fixed' => true, 'inherit' => true, 'systemColumn' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'explanation' => 'tl_iso_product.type',
            'wizard' => [[Wizard::class, 'onProductTypeWizard']],
        ],
        'pages' => [
            'exclude' => true,
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
            'exclude' => true,
            'inputType' => 'inheritCheckbox',
            'eval' => ['multiple' => true],
            'attributes' => ['systemColumn' => true],
            'sql' => 'blob NULL',
        ],
        'fallback' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'variant_fixed' => true, 'excluded' => true, 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
            'save_callback' => [
                [Fallback::class, 'reset'],
            ],
        ],
        'alias' => [
            'exclude' => true,
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
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 14, 'unique' => true, 'doNotCopy' => true, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'general_legend', 'fe_search' => true, 'singular' => true],
            'sql' => "varchar(14) NOT NULL default ''",
        ],
        'sku' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'unique' => true, 'doNotCopy' => true, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'general_legend', 'fe_sorting' => true, 'fe_search' => true, 'singular' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'name' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'clr long'],
            'attributes' => ['legend' => 'general_legend', 'multilingual' => true, 'fixed' => true, 'fe_sorting' => true, 'fe_search' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'teaser' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:80px', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'multilingual' => true, 'fe_search' => true],
            'sql' => 'text NULL',
        ],
        'description' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'general_legend', 'multilingual' => true, 'fe_search' => true],
            'sql' => 'text NULL',
        ],
        'meta_title' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
            'attributes' => ['legend' => 'meta_legend', 'multilingual' => true, 'variant_excluded' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'meta_description' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:60px', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'meta_legend', 'multilingual' => true, 'variant_excluded' => true],
            'sql' => 'text NULL',
        ],
        'meta_keywords' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:40px', 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'meta_legend', 'multilingual' => true, 'variant_excluded' => true],
            'sql' => 'text NULL',
        ],
        'price' => [
            'exclude' => true,
            'inputType' => 'timePeriod',
            'foreignKey' => TaxClass::getTable().'.name',
            'eval' => ['mandatory' => true, 'maxlength' => 13, 'rgxp' => 'digit', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'doNotSaveEmpty' => true, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'pricing_legend', 'fe_sorting' => true, 'dynamic' => true, 'singular' => true, 'systemColumn' => true, 'type' => Price::class],
            'load_callback' => [
                [Backend\Product\Price::class, 'load'],
            ],
            'save_callback' => [
                [Backend\Product\Price::class, 'save'],
            ],
        ],
        'prices' => [
            'inputType' => 'dcaWizard',
            'foreignTable' => ProductPrice::getTable(),
            'attributes' => ['systemColumn' => true],
            'eval' => [
                'listCallback' => [Backend\ProductPrice\Callback::class, 'generateWizardList'],
                'list_callback' => [Backend\ProductPrice\Callback::class, 'generateWizardList'],
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
            'exclude' => true,
            'inputType' => 'timePeriod',
            'foreignKey' => 'tl_iso_baseprice.name',
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'attributes' => ['type' => BasePrice::class, 'legend' => 'pricing_legend'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'shipping_weight' => [
            'exclude' => true,
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'attributes' => ['legend' => 'shipping_legend', 'type' => Weight::class],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'shipping_exempt' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend', 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'shipping_pickup' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend', 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'shipping_price' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'shipping_legend', 'systemColumn' => true, 'type' => ShippingPrice::class],
            'sql' => "decimal(9,2) unsigned NOT NULL default '0.00'",
        ],
        'images' => [
            'exclude' => true,
            'inputType' => 'mediaManager',
            'explanation' => 'mediaManager',
            'eval' => ['extensions' => '%contao.image.valid_extensions%', 'helpwizard' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'media_legend', 'fixed' => true, 'multilingual' => true, 'dynamic' => true, 'systemColumn' => true, 'fetch_fallback' => true],
            'sql' => 'blob NULL',
        ],
        'protected' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'attributes' => ['legend' => 'expert_legend', 'systemColumn' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true, 'systemColumn' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'cssID' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['multiple' => true, 'size' => 2, 'tl_class' => 'w50'],
            'attributes' => ['legend' => 'expert_legend'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'exclude' => true,
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
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'attributes' => ['legend' => 'publish_legend', 'fixed' => true, 'variant_fixed' => true, 'systemColumn' => true],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'exclude' => true,
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
    unset(
        $GLOBALS['TL_DCA']['tl_iso_product']['list']['global_operations']['import'],
        $GLOBALS['TL_DCA']['tl_iso_product']['list']['global_operations']['groups'],
    );
} else {
    unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['global_operations']['generate']);
}
