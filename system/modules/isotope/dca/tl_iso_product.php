<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

\System::loadLanguageFile(\Isotope\Model\ProductType::getTable());

/**
 * Table tl_iso_product
 */
$GLOBALS['TL_DCA']['tl_iso_product'] = array
(

    // Config
    'config' => array
    (
        'label'                     => &$GLOBALS['TL_LANG']['MOD']['iso_products'][0],
        'dataContainer'             => 'ProductData',
        'enableVersioning'          => true,
        'switchToEdit'              => true,
        'ctable'                    => array(\Isotope\Model\Download::getTable(), \Isotope\Model\ProductCategory::getTable(), \Isotope\Model\ProductPrice::getTable(), \Isotope\Model\AttributeOption::getTable()),
        'onload_callback' => array
        (
            array('Isotope\Backend\Product\DcaManager', 'load'),
            array('Isotope\Backend\Product\Permission', 'check'),
            array('Isotope\Backend\Product\Panel', 'applyAdvancedFilters'),
            array('Isotope\Backend\Product\XmlSitemap', 'generate'),
        ),
        'oncreate_callback' => array
        (
            array('Isotope\Backend\Product\DcaManager', 'updateNewRecord'),
        ),
        'oncopy_callback' => array
        (
            array('Isotope\Backend\Product\Category', 'updateSorting'),
            array('Isotope\Backend\Product\DcaManager', 'updateDateAdded'),
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\Backend', 'truncateProductCache'),
            array('Isotope\Backend\Product\XmlSitemap', 'scheduleUpdate'),
        ),
        'onversion_callback' => array
        (
            array('Isotope\Backend\Product\Category', 'createVersion'),
            // price version callbacks are added in the onload_callback (buildPaletteString)
        ),
        'onrestore_callback' => array
        (
            array('Isotope\Backend\Product\Category', 'restoreVersion'),
            // price version callbacks are added in the onload_callback (buildPaletteString)
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'                                    => 'primary',
                'gid'                                   => 'index',
                'pid,language'                          => 'index',
                'language,fallback'                     => 'index',
                'language,published,start,stop,pid'     => 'index',
                'start'                                 => 'index',
                'sku'                                   => 'index',
                'gtin'                                  => 'index',
            )
        ),
    ),

    // Select
    'select' => array
    (
        'buttons_callback' => array
        (
            array('Isotope\Backend\Product\Button', 'forSelect'),
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 2,
            'fields'                => array('name'),
            'headerFields'          => array('name', 'sku', 'price', 'published'),
            'flag'                  => 1,
            'panelLayout'           => 'iso_buttons,iso_filter;filter;sort,iso_sorting,search,limit',
            'panel_callback'        => array
            (
                'iso_buttons' => array('Isotope\Backend\Product\Panel', 'generateFilterButtons'),
                'iso_filter'  => array('Isotope\Backend\Product\Panel', 'generateAdvancedFilters'),
                'iso_sorting'  => array('Isotope\Backend\Product\Panel', 'generateSortingIcon'),
            )
        ),
        'label' => array
        (
            'fields'                => array('images', 'name', 'sku', 'price'),
            'showColumns'           => true,
            'label_callback'        => array('Isotope\Backend\Product\Label', 'generate'),
        ),
        'global_operations' => array
        (
            'generate' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product']['generate'],
                'href'              => 'key=generate',
                'icon'              => 'new.svg',
            ),
            'groups' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product']['product_groups'],
                'href'              => 'table='.\Isotope\Model\Group::getTable(),
                'icon'              => 'system/modules/isotope/assets/images/folders.png',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forGroups')
            ),
            'import' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product']['import'],
                'href'              => 'key=import',
                'icon'              => 'system/modules/isotope/assets/images/image--plus.png',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'              => 'act=edit',
                'icon'              => 'edit.svg',
            ),
            'copy' => array
            (
                'href'              => 'act=copy&amp;childs=1',
                'icon'              => 'copy.svg',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forCopy')
            ),
            'cut' => array
            (
                'href'              => 'act=cut',
                'icon'              => 'cut.svg',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forCut')
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.svg',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forDelete')
            ),
            'toggle' => array
            (
                'icon'              => 'visible.svg',
                'attributes'        => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forVisibilityToggle')
            ),
            'show' => array
            (
                'href'              => 'act=show',
                'icon'              => 'show.svg'
            ),
            'break' => array
            (
                'button_callback'   => function() {
                    return '<br>';
                }
            ),
            'fallback' => array
            (
                'href'              => 'key=fallback',
                'icon'              => 'featured.svg',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forFallbackToggle')
            ),
            'variants' => array
            (
                'href'              => '',
                'icon'              => 'system/modules/isotope/assets/images/table--pencil.png',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forVariants'),
            ),
            'related' => array
            (
                'href'              => 'table='.\Isotope\Model\RelatedProduct::getTable(),
                'icon'              => 'system/modules/isotope/assets/images/sitemap.png',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forRelated'),
            ),
            'downloads' => array
            (
                'href'              => 'table='.\Isotope\Model\Download::getTable(),
                'icon'              => 'system/modules/isotope/assets/images/paper-clip.png',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forDownloads'),
            ),
            'group' => array
            (
                'href'              => 'act=cut',
                'icon'              => 'system/modules/isotope/assets/images/folder-network.png',
                'button_callback'   => array('Isotope\Backend\Product\Button', 'forGroup'),
            ),
        ),
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type', 'protected'),
        'default'                   => '{general_legend},type',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'protected'                 => 'groups',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'attributes'            => array('systemColumn'=>true),
            'sql'                   => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            // Fix for DC_Table, otherwise getPalette() will not use the PID value
            'eval'                  => array('submitOnChange'=>true),
            'attributes'            => array('systemColumn'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'gid' => array
        (
            'foreignKey'            => \Isotope\Model\Group::getTable().'.name',
            'eval'                  => array('doNotShow'=>true),
            'attributes'            => array('systemColumn'=>true, 'inherit'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'tstamp' => array
        (
            'sorting'               => true,
            'attributes'            => array('systemColumn'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'language' => array
        (
            'eval'                  => array('doNotShow'=>true),
            'attributes'            => array('systemColumn'=>true, 'inherit'=>true),
            'sql'                   => "varchar(5) NOT NULL default ''",
        ),
        'dateAdded' => array
        (
            'sorting'               => true,
            'eval'                  => array('rgxp'=>'datim', 'doNotCopy'=>true),
            'attributes'            => array('fe_sorting'=>true, 'systemColumn'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'hitCount' => array
        (
            'sorting'               => true,
            'flag'                  => 12,
            'attributes'            => array('fe_sorting'=>true, 'systemColumn'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'type' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'options_callback'      => array('Isotope\Backend\ProductType\Callback', 'getOptions'),
            'foreignKey'            => \Isotope\Model\ProductType::getTable().'.name',
            'eval'                  => array('mandatory'=>true, 'submitOnChange'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50 wizard', 'helpwizard'=>true),
            'attributes'            => array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true, 'systemColumn'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
            'explanation'           => 'tl_iso_product.type',
            'wizard'                => [['Isotope\Backend\Product\Wizard', 'onProductTypeWizard']],
        ),
        'pages' => array
        (
            'exclude'               => true,
            'inputType'             => 'pageTree',
            'foreignKey'            => 'tl_page.title',
            'eval'                  => array('doNotSaveEmpty'=>true, 'multiple'=>true, 'fieldType'=>'checkbox', 'orderField'=>'orderPages', 'tl_class'=>'clr hide_sort_hint'),
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
            'attributes'            => array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true, 'systemColumn'=>true),
            'load_callback'         => array
            (
                array('Isotope\Backend\Product\Category', 'load'),
            ),
            'save_callback' => array
            (
                array('Isotope\Backend\Product\Category', 'save'),
            ),
        ),
        'orderPages' => array
        (
            'eval'                  => array('doNotShow'=>true),
            'attributes'            => array('systemColumn'=>true, 'inherit'=>true),
            'sql'                   => "text NULL"
        ),
        'inherit' => array
        (
            'exclude'               => true,
            'inputType'             => 'inheritCheckbox',
            'eval'                  => array('multiple'=>true),
            'attributes'            => array('systemColumn'=>true),
            'sql'                   => "blob NULL",
        ),
        'fallback' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('doNotCopy'=>true, 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'general_legend', 'variant_fixed'=>true, 'excluded'=>true, 'systemColumn'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\Backend\Product\Fallback', 'reset')
            ),
        ),
        'alias' => array
        (
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'alnum', 'doNotCopy'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
            'sql'                   => "varchar(128) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\Backend\Product\Alias', 'save'),
            ),
        ),
        'gtin' => array
        (
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>14, 'unique'=>true, 'doNotCopy'=>true, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'general_legend', 'fe_search'=>true, 'singular'=>true),
            'sql'                   => "varchar(14) NOT NULL default ''",
        ),
        'sku' => array
        (
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'unique'=>true, 'doNotCopy'=>true, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'general_legend', 'fe_sorting'=>true, 'fe_search'=>true, 'singular'=>true),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'name' => array
        (
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'clr long'),
            'attributes'            => array('legend'=>'general_legend', 'multilingual'=>true, 'fixed'=>true, 'fe_sorting'=>true, 'fe_search'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'teaser' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:80px', 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'general_legend', 'multilingual'=>true, 'fe_search'=>true),
            'sql'                   => "text NULL",
        ),
        'description' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'textarea',
            'eval'                  => array('mandatory'=>true, 'rte'=>'tinyMCE', 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'general_legend', 'multilingual'=>true, 'fe_search'=>true),
            'sql'                   => "text NULL",
        ),
        'meta_title' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'clr long'),
            'attributes'            => array('legend'=>'meta_legend', 'multilingual'=>true, 'variant_excluded'=>true),
            'sql'                   =>  "varchar(255) NOT NULL default ''",
        ),
        'meta_description' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:60px', 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'meta_legend', 'multilingual'=>true, 'variant_excluded'=>true),
            'sql'                   =>  "text NULL",
        ),
        'meta_keywords' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:40px', 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'meta_legend', 'multilingual'=>true, 'variant_excluded'=>true),
            'sql'                   => "text NULL",
        ),
        'price' => array
        (
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'foreignKey'            => \Isotope\Model\TaxClass::getTable().'.name',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>13, 'rgxp'=>'digit', 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['MSC']['taxFree'], 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'pricing_legend', 'fe_sorting'=>true, 'dynamic'=>true, 'singular'=>true, 'systemColumn'=>true, 'type'=>'\Isotope\Model\Attribute\Price'),
            'load_callback' => array
            (
                array('\Isotope\Backend\Product\Price', 'load'),
            ),
            'save_callback' => array
            (
                array('\Isotope\Backend\Product\Price', 'save'),
            ),
        ),
        'prices' => array
        (
            'inputType'             => 'dcaWizard',
            'foreignTable'          => \Isotope\Model\ProductPrice::getTable(),
            'attributes'            => array('systemColumn'=>true),
            'eval'                  => array
            (
                'listCallback'      => array('Isotope\Backend\ProductPrice\Callback', 'generateWizardList'),
                'applyButtonLabel'  => &$GLOBALS['TL_LANG']['tl_iso_product']['prices']['apply_and_close'],
                'global_operations' => array('new'),
                'operations'        => array('edit', 'copy', 'delete', 'show'),
                'tl_class'          =>'clr',
            ),
        ),
        'price_tiers' => array
        (
            // This is only for automated table generation in the frontend
            'attributes'            => array('type'=>'\Isotope\Model\Attribute\PriceTiers'),
            'tableformat' => array
            (
                'min' => array
                (
                    'label'         => &$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['min'],
                    'format'        => &$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['min_format'],
                ),
                'price' => array
                (
                    'label'         => &$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['price'],
                    'rgxp'          => 'digit'
                ),
                'tax_class' => array
                (
                    'doNotShow'     => true,
                ),
            ),
        ),
        'baseprice' => array
        (
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'foreignKey'            => 'tl_iso_baseprice.name',
            'eval'                  => array('includeBlankOption'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'attributes'            => array('type'=>'\Isotope\Model\Attribute\BasePrice', 'legend'=>'pricing_legend'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'shipping_weight' => array
        (
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'default'               => array('unit'=>'kg'),
            'options'               => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'             => &$GLOBALS['TL_LANG']['WGT'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50', 'helpwizard'=>true),
            'attributes'            => array('legend'=>'shipping_legend', 'type'=>'\Isotope\Model\Attribute\Weight'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'shipping_exempt' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'attributes'            => array('legend'=>'shipping_legend', 'systemColumn'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'shipping_pickup' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'attributes'            => array('legend'=>'shipping_legend', 'systemColumn'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'shipping_price' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'shipping_legend', 'systemColumn'=>true, 'type'=>'\Isotope\Model\Attribute\ShippingPrice'),
            'sql'                   => "decimal(9,2) unsigned NOT NULL default '0.00'",
        ),
        'images' => array
        (
            'exclude'               => true,
            'inputType'             => 'mediaManager',
            'explanation'           => 'mediaManager',
            'eval'                  => array('extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'helpwizard'=>true, 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'media_legend', 'fixed'=>true, 'multilingual'=>true, 'dynamic'=>true, 'systemColumn'=>true, 'fetch_fallback'=>true),
            'sql'                   => "blob NULL",
        ),
        'protected' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'expert_legend', 'systemColumn'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'groups' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'foreignKey'            => 'tl_member_group.name',
            'eval'                  => array('mandatory'=>true, 'multiple'=>true, 'systemColumn'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'guests' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'attributes'            => array('legend'=>'expert_legend', 'systemColumn'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'cssID' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('multiple'=>true, 'size'=>2, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'expert_legend'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'published' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('doNotCopy'=>true, 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true, 'systemColumn'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\Backend', 'truncateProductCache'),
            ),
        ),
        'start' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'attributes'            => array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true, 'systemColumn'=>true),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'stop' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'attributes'            => array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true, 'systemColumn'=>true),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'variantFields' => array
        (
            'label'                 => &$GLOBALS['TL_LANG'][\Isotope\Model\ProductType::getTable()]['variant_attributes'],
        ),
        'source' => array
        (
            'eval'                  => array('mandatory'=>true, 'required'=>true, 'fieldType'=>'radio'),
        ),
    ),
);


/**
 * Adjust the data configuration array in variants view
 */
if (\Contao\Input::get('id')) {
    $GLOBALS['TL_LANG']['tl_iso_product']['new'] = &$GLOBALS['TL_LANG']['tl_iso_product']['new_variant'];
    $GLOBALS['TL_DCA']['tl_iso_product']['config']['switchToEdit'] = false;
    unset(
        $GLOBALS['TL_DCA']['tl_iso_product']['list']['global_operations']['import'],
        $GLOBALS['TL_DCA']['tl_iso_product']['list']['global_operations']['groups']
    );
} else {
    unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['global_operations']['generate']);
}
