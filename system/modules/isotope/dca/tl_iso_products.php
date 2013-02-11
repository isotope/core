<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */


/**
 * Table tl_iso_products
 */
$GLOBALS['TL_DCA']['tl_iso_products'] = array
(

    // Config
    'config' => array
    (
        'label'						=> &$GLOBALS['TL_LANG']['MOD']['iso_products'][0],
        'dataContainer'				=> 'ProductData',
        'enableVersioning'			=> true,
        'closed'					=> true,
        'gtable'					=> 'tl_iso_groups',
        'ctable'					=> array('tl_iso_downloads', 'tl_iso_product_categories', 'tl_iso_prices'),
        'onload_callback' => array
        (
            array('Isotope\ProductCallbacks', 'applyAdvancedFilters'),
            array('Isotope\ProductCallbacks', 'checkPermission'),
            array('Isotope\ProductCallbacks', 'buildPaletteString'),
            array('Isotope\ProductCallbacks', 'loadDefaultProductType')
        ),
        'oncopy_callback' => array
        (
            array('Isotope\ProductCallbacks', 'updateCategorySorting'),
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\Backend', 'truncateProductCache'),
            array('Isotope\ProductCallbacks', 'storeDateAdded')
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'					=> 5,
            'fields'				=> array('name'),
            'flag'					=> 1,
            'panelLayout'			=> 'filter;sort,search',
            'icon'					=> 'system/modules/isotope/assets/store-open.png',
            'paste_button_callback'	=> array('Isotope\PasteProductButton', 'generate'),
            'rootPaste'				=> true,
        ),
        'label' => array
        (
            'fields'				=> array('name'),
            'format'				=> '%s',
            'label_callback'		=> array('Isotope\ProductCallbacks', 'getRowLabel'),
        ),
        'global_operations' => array
        (
            'new_product' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['new_product'],
                'href'				=> 'act=paste&mode=create&type=product',
                'class'				=> 'header_new',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
            ),
            'new_variant' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['new_variant'],
                'href'				=> 'act=paste&mode=create&type=variant',
                'class'				=> 'header_new',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
            ),
            'filter' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['filter'],
                'class'				=> ('header_iso_filter' . (is_array(\Input::get('filter')) ? ' header_iso_filter_active' : '')),
                'attributes'		=> 'onclick="Backend.getScrollOffset();" style="display:none"',
            ),
            'filter_noimages' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['filter_noimages'],
                'href'				=> 'filter[]=noimages',
                'class'				=> 'header_iso_filter_noimages isotope-filter',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'filterButton'),
            ),
            'filter_nocategory' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['filter_nocategory'],
                'href'				=> 'filter[]=nocategory',
                'class'				=> 'header_iso_filter_nocategory isotope-filter',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'filterButton'),
            ),
            'filter_new_today' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_today'],
                'href'				=> 'filter[]=new_today',
                'class'				=> 'header_iso_filter_new_today isotope-filter',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'filterButton'),
            ),
            'filter_new_week' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_week'],
                'href'				=> 'filter[]=new_week',
                'class'				=> 'header_iso_filter_new_week isotope-filter',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'filterButton'),
            ),
            'filter_new_month' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_month'],
                'href'				=> 'filter[]=new_month',
                'class'				=> 'header_iso_filter_new_month isotope-filter',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'filterButton'),
            ),
            'filter_remove' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['filter_remove'],
                'href'				=> 'filter[]=test',
                'class'				=> 'header_iso_filter_remove isotope-filter',
                'attributes'		=> ('onclick="Backend.getScrollOffset();"' . (is_array(\Input::get('filter')) ? '' : ' style="display:none"')),
                'button_callback'	=> array('Isotope\ProductCallbacks', 'filterRemoveButton'),
            ),
            'tools' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['tools'],
                'class'				=> 'header_isotope_tools',
                'attributes'		=> 'onclick="Backend.getScrollOffset();" style="display:none"',
            ),
            'all' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'				=> 'act=select',
                'class'				=> 'header_edit_all isotope-tools',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"'
            ),
            'toggleGroups' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['toggleGroups'],
                'href'				=> 'gtg=all',
                'class'				=> 'header_toggle isotope-tools',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'toggleGroups')
            ),
            'toggleVariants' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['toggleVariants'],
                'href'				=> 'ptg=all',
                'class'				=> 'header_toggle isotope-tools',
                'attributes'		=> 'onclick="Backend.getScrollOffset(); Isotope.purgeProductsStorage();"',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'toggleVariants')
            ),
            'groups' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['groups'],
                'href'				=> 'table=tl_iso_groups',
                'class'				=> 'header_iso_groups isotope-tools',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'groupsButton')
            ),
            'import' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['import'],
                'href'				=> 'key=import',
                'class'				=> 'header_import_assets isotope-tools',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['edit'],
                'href'				=> 'act=edit',
                'icon'				=> 'edit.gif',
            ),
            'copy' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['copy'],
                'href'				=> 'act=paste&amp;mode=copy&amp;childs=1',
                'icon'				=> 'copy.gif',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
            ),
            'cut' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['cut'],
                'href'				=> 'act=paste&amp;mode=cut',
                'icon'				=> 'cut.gif',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"',
            ),
            'delete' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['delete'],
                'href'				=> 'act=delete',
                'icon'				=> 'delete.gif',
                'attributes'		=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ),
            'toggle' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['toggle'],
                'icon'				=> 'visible.gif',
                'attributes'		=> 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback'	=> array('Isotope\tl_iso_products', 'toggleIcon')
            ),
            'show' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['show'],
                'href'				=> 'act=show',
                'icon'				=> 'show.gif'
            ),
            'tools' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['tools'],
                'icon'				=> 'system/modules/isotope/assets/lightning.png',
                'attributes'		=> 'class="invisible isotope-contextmenu"',
            ),
            'quick_edit' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['quick_edit'],
                'href'				=> 'key=quick_edit',
                'icon'				=> 'system/modules/isotope/assets/table-select-cells.png',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'quickEditButton'),
                'attributes'		=> 'class="isotope-tools"',
            ),
            'generate' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['generate'],
                'href'				=> 'key=generate',
                'icon'				=> 'system/modules/isotope/assets/table-insert-row.png',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'generateButton'),
                'attributes'		=> 'class="isotope-tools"',
            ),
            'related' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['related'],
                'href'				=> 'table=tl_iso_related_products',
                'icon'				=> 'system/modules/isotope/assets/sitemap.png',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'relatedButton'),
                'attributes'		=> 'class="isotope-tools"',
            ),
            'downloads' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['downloads'],
                'href'				=> 'table=tl_iso_downloads',
                'icon'				=> 'system/modules/isotope/assets/paper-clip.png',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'downloadsButton'),
                'attributes'		=> 'class="isotope-tools"',
            ),
            'prices' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['prices'],
                'href'				=> 'table=tl_iso_prices',
                'icon'				=> 'system/modules/isotope/assets/price-tag.png',
                'button_callback'	=> array('Isotope\ProductCallbacks', 'pricesButton'),
                'attributes'		=> 'class="isotope-tools"',
            ),
        ),
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'				=> array('type', 'pid', 'protected'),
        'default'					=> '{general_legend},type',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'protected'					=> 'groups',
    ),

    // Fields
    'fields' => array
    (
        'pid' => array
        (
            // Fix for DC_Table, otherwise getPalette() will not use the PID value
            'eval'					=> array('submitOnChange'=>true),
        ),
        'dateAdded' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'eval'					=> array('rgxp'=>'datim'),
            'attributes'			=> array('fe_sorting'=>true),
        ),
        'type' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['type'],
            'exclude'               => true,
            'filter'				=> true,
            'inputType'				=> 'select',
            'options_callback'		=> array('Isotope\ProductCallbacks', 'getProductTypes'),
            'foreignKey'			=> (strlen(\Input::get('table')) ? 'tl_iso_producttypes.name' : null),
            'eval'					=> array('mandatory'=>true, 'submitOnChange'=>true, 'includeBlankOption'=>true, 'tl_class'=>'clr'),
            'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
        ),
        'pages' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['pages'],
            'exclude'               => true,
            'filter'				=> true,
            'inputType'				=> 'pageTree',
            'foreignKey'			=> 'tl_page.title',
            'eval'					=> array('mandatory'=>false, 'multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'clr'),
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
            'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
            'load_callback'			=> array
            (
                array('Isotope\ProductCallbacks', 'loadProductCategories'),
            ),
            'save_callback'			=> array
            (
                array('Isotope\ProductCallbacks', 'saveProductCategories'),
            ),
        ),
        'inherit' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['inherit'],
            'exclude'               => true,
            'inputType'				=> 'inheritCheckbox',
            'eval'					=> array('multiple'=>true, 'doNotShow'=>true),
        ),
        'alias' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['alias'],
            'exclude'               => true,
            'search'				=> true,
            'sorting'				=> true,
            'inputType'				=> 'text',
            'eval'					=> array('rgxp'=>'alnum', 'doNotCopy'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
            'save_callback' => array
            (
                array('Isotope\ProductCallbacks', 'generateAlias'),
            ),
        ),
        'sku' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['sku'],
            'exclude'               => true,
            'search'				=> true,
            'sorting'				=> true,
            'inputType'				=> 'text',
            'eval'					=> array('mandatory'=>true, 'maxlength'=>128, 'unique'=>true, 'tl_class'=>'w50'),
            'attributes'			=> array('legend'=>'general_legend', 'fe_sorting'=>true, 'fe_search'=>true),
        ),
        'name' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['name'],
            'exclude'               => true,
            'search'				=> true,
            'sorting'				=> true,
            'inputType'				=> 'text',
            'eval'					=> array('mandatory'=>true, 'tl_class'=>'clr long'),
            'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true, 'fixed'=>true, 'fe_sorting'=>true, 'fe_search'=>true),
        ),
        'teaser' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['teaser'],
            'exclude'               => true,
            'search'				=> true,
            'inputType'				=> 'textarea',
            'eval'					=> array('style'=>'height:80px', 'tl_class'=>'clr'),
            'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true),
        ),
        'description' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['description'],
            'exclude'               => true,
            'search'				=> true,
            'inputType'				=> 'textarea',
            'eval'					=> array('mandatory'=>true, 'rte'=>'tinyMCE', 'tl_class'=>'clr'),
            'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true, 'fe_search'=>true),
        ),
        'description_meta' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['description_meta'],
            'exclude'               => true,
            'search'				=> true,
            'inputType'				=> 'textarea',
            'eval'					=> array('style'=>'height:60px'),
            'attributes'			=> array('legend'=>'meta_legend', 'multilingual'=>true),
        ),
        'keywords_meta' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['keywords_meta'],
            'exclude'               => true,
            'search'				=> true,
            'inputType'				=> 'textarea',
            'eval'					=> array('style'=>'height:40px'),
            'attributes'			=> array('legend'=>'meta_legend', 'multilingual'=>true),
        ),
        'price' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['price'],
            'exclude'               => true,
            'inputType'				=> 'text',
            'eval'					=> array('mandatory'=>true, 'maxlength'=>13, 'rgxp'=>'price', 'tl_class'=>'w50'),
            'attributes'			=> array('legend'=>'pricing_legend', 'fe_sorting'=>true, 'dynamic'=>true),
        ),
        'prices' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['prices'],
            'inputType'				=> 'dcaWizard',
            'foreignTable'			=> 'tl_iso_prices',
            'eval'					=> array('tl_class'=>'clr'),
        ),
        'price_tiers' => array
        (
            'eval'					=> array('dynamic'=>true),
            'tableformat' => array
            (
                'min'		=> array
                (
                    'label'			=> &$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min'],
                    'format'		=> &$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min_format'],
                ),
                'price'		=> array
                (
                    'label'			=> &$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['price'],
                    'rgxp'			=> 'price'
                ),
                'tax_class'	=> array
                (
                    'doNotShow'		=> true,
                ),
            ),
        ),
        'tax_class' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['tax_class'],
            'exclude'               => true,
            'inputType'				=> 'select',
            'foreignKey'			=> 'tl_iso_tax_class.name',
            'attributes'			=> array('legend'=>'pricing_legend', 'tl_class'=>'w50'),
            'eval'					=> array('includeBlankOption'=>true, 'dynamic'=>true),
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'baseprice' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['baseprice'],
            'exclude'               => true,
            'inputType'				=> 'timePeriod',
            'foreignKey'			=> 'tl_iso_baseprice.name',
            'eval'					=> array('includeBlankOption'=>true),
            'attributes'			=> array('legend'=>'pricing_legend', 'tl_class'=>'w50'),
        ),
        'shipping_weight' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight'],
            'exclude'               => true,
            'inputType'				=> 'timePeriod',
            'default'				=> array('', 'kg'),
            'options'				=> array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'				=> &$GLOBALS['TL_LANG']['WGT'],
            'eval'					=> array('rgxp'=>'digit', 'tl_class'=>'w50 wizard', 'helpwizard'=>true),
            'attributes'			=> array('legend'=>'shipping_legend'),
        ),
        'shipping_exempt' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt'],
            'exclude'               => true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('tl_class'=>'w50'),
            'attributes'			=> array('legend'=>'shipping_legend'),
        ),
        'images' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['images'],
            'exclude'               => true,
            'inputType'				=> 'mediaManager',
            'explanation'			=> 'mediaManager',
            'eval'					=> array('extensions'=>'jpeg,jpg,png,gif', 'helpwizard'=>true, 'tl_class'=>'clr'),
            'attributes'			=> array('legend'=>'media_legend', 'fixed'=>true, 'multilingual'=>true, 'dynamic'=>true, 'fetch_fallback'=>true),
        ),
        'protected' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['protected'],
            'exclude'               => true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
            'attributes'			=> array('legend'=>'expert_legend'),
        ),
        'groups' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['groups'],
            'exclude'               => true,
            'inputType'				=> 'checkbox',
            'foreignKey'			=> 'tl_member_group.name',
            'eval'					=> array('mandatory'=>true, 'multiple'=>true),
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'guests' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['guests'],
            'exclude'               => true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('tl_class'=>'w50'),
            'attributes'			=> array('legend'=>'expert_legend'),
        ),
        'cssID' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['cssID'],
            'exclude'               => true,
            'inputType'				=> 'text',
            'eval'					=> array('multiple'=>true, 'size'=>2, 'tl_class'=>'w50'),
            'attributes'			=> array('legend'=>'expert_legend'),
        ),
        'published' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['published'],
            'exclude'               => true,
            'filter'				=> true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('doNotCopy'=>true, 'tl_class'=>'clr'),
            'attributes'			=> array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true),
            'save_callback' => array
            (
                array('Isotope\Backend', 'truncateProductCache'),
            ),
        ),
        'start' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['start'],
            'exclude'               => true,
            'inputType'				=> 'text',
            'eval'					=> array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'attributes'			=> array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true),
        ),
        'stop' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['stop'],
            'exclude'               => true,
            'inputType'				=> 'text',
            'eval'					=> array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'attributes'			=> array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true),
        ),
        'source' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['source'],
            'eval'					=> array('mandatory'=>true, 'required'=>true, 'fieldType'=>'radio'),
        ),
        'variant_attributes' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['variant_attributes'],
            'inputType'				=> 'variantWizard',
            'options'				=> array(),
            'eval'					=> array('doNotSaveEmpty'=>true),
        ),
    ),
);
