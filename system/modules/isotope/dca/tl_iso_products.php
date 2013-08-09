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
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */


/**
 * Table tl_iso_products
 */
$GLOBALS['TL_DCA']['tl_iso_products'] = array
(

    // Config
    'config' => array
    (
        'label'                     => &$GLOBALS['TL_LANG']['MOD']['iso_products'][0],
        'dataContainer'             => 'ProductData',
        'enableVersioning'          => true,
        'closed'                    => true,
        'switchToEdit'              => true,
        'gtable'                    => 'tl_iso_groups',
        'ctable'                    => array('tl_iso_downloads', 'tl_iso_product_categories', 'tl_iso_prices'),
        'onload_callback' => array
        (
            array('Isotope\ProductCallbacks', 'applyAdvancedFilters'),
            array('Isotope\ProductCallbacks', 'checkPermission'),
            array('Isotope\ProductCallbacks', 'buildPaletteString'),
            array('Isotope\ProductCallbacks', 'loadDefaultProductType'),
            array('Isotope\ProductCallbacks', 'addMoveAllFeature'),
            array('Isotope\ProductCallbacks', 'changeVariantColumns'),
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
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'gid' => 'index',
                'pid;pid,language,published' => 'index',
            )
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
            'headerOperations'      => array('edit', 'copy', 'cut', 'delete', 'toggle', 'show', 'quick_edit', 'generate', 'related', 'downloads', 'prices'),
            'flag'                  => 1,
            'panelLayout'           => 'iso_buttons,iso_filter;filter;sort,search,limit',
            'icon'                  => 'system/modules/isotope/assets/store-open.png',
            'paste_button_callback' => array('Isotope\PasteProductButton', 'generate'),
            'panel_callback'        => array
            (
            	'iso_buttons' => array('Isotope\ProductCallbacks', 'generateFilterButtons'),
            	'iso_filter'  => array('Isotope\ProductCallbacks', 'generateAdvancedFilters')
            )
        ),
        'label' => array
        (
            'fields'                => array('images', 'name', 'sku', 'price'),
            'showColumns'           => true,
            'label_callback'        => array('Isotope\ProductCallbacks', 'getRowLabel'),
        ),
        'global_operations' => array
        (
            'new_product' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['new_product'],
                'href'              => 'act=create&type=product',
                'icon'              => 'new.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'groups' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['groups'],
                'href'              => 'table=tl_iso_groups',
                'icon'              => 'system/modules/isotope/assets/folders.png',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\ProductCallbacks', 'groupsButton')
            ),
            'import' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['import'],
                'href'              => 'key=import',
                'icon'              => 'system/modules/isotope/assets/image--plus.png',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif',
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['copy'],
                'href'              => 'act=copy&amp;childs=1',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\tl_iso_products', 'copyIcon')
            ),
            'cut' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['cut'],
                'href'              => 'act=cut',
                'icon'              => 'cut.gif',
                'button_callback'   => array('Isotope\tl_iso_products', 'cutIcon')
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ),
            'toggle' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['toggle'],
                'icon'              => 'visible.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback'   => array('Isotope\tl_iso_products', 'toggleIcon')
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            ),
            'quick_edit' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['quick_edit'],
                'href'              => 'key=quick_edit',
                'icon'              => 'system/modules/isotope/assets/table-select-cells.png',
                'button_callback'   => array('Isotope\ProductCallbacks', 'variantsButton'),
            ),
            'generate' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['generate'],
                'href'              => 'key=generate',
                'icon'              => 'system/modules/isotope/assets/table-insert-row.png',
                'button_callback'   => array('Isotope\ProductCallbacks', 'variantsButton'),
            ),
            'related' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['related'],
                'href'              => 'table=tl_iso_related_products',
                'icon'              => 'system/modules/isotope/assets/sitemap.png',
                'button_callback'   => array('Isotope\ProductCallbacks', 'relatedButton'),
            ),
            'downloads' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['downloads'],
                'href'              => 'table=tl_iso_downloads',
                'icon'              => 'system/modules/isotope/assets/paper-clip.png',
                'button_callback'   => array('Isotope\ProductCallbacks', 'downloadsButton'),
            ),
            'prices' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_products']['prices'],
                'href'              => 'table=tl_iso_prices',
                'icon'              => 'system/modules/isotope/assets/price-tag.png',
                'button_callback'   => array('Isotope\ProductCallbacks', 'pricesButton'),
            ),
        ),
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type', 'pid', 'protected'),
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
            'sql'                  => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            // Fix for DC_Table, otherwise getPalette() will not use the PID value
            'eval'                  => array('submitOnChange'=>true),
            'sql'                  => "int(10) unsigned NOT NULL default '0'",
        ),
        'gid' => array
        (
            'sql'                  => "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array
        (
            'sql'                  => "int(10) unsigned NOT NULL default '0'",
        ),
        'language' => array
        (
            'sql'                  => "varchar(5) NOT NULL default ''",
        ),
        'dateAdded' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'eval'                  => array('rgxp'=>'datim', 'doNotCopy'=>true),
            'attributes'            => array('fe_sorting'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'type' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['type'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'options_callback'      => array('Isotope\ProductCallbacks', 'getProductTypes'),
            'foreignKey'            => (strlen(\Input::get('table')) ? 'tl_iso_producttypes.name' : null),
            'eval'                  => array('mandatory'=>true, 'submitOnChange'=>true, 'includeBlankOption'=>true, 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'pages' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['pages'],
            'exclude'               => true,
            'inputType'             => 'pageTree',
            'foreignKey'            => 'tl_page.title',
            'eval'                  => array('mandatory'=>false, 'multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'clr'),
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
            'attributes'            => array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
            'load_callback'         => array
            (
                array('Isotope\ProductCallbacks', 'loadProductCategories'),
            ),
            'save_callback' => array
            (
                array('Isotope\ProductCallbacks', 'saveProductCategories'),
            ),
        ),
        'inherit' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['inherit'],
            'exclude'               => true,
            'inputType'             => 'inheritCheckbox',
            'eval'                  => array('multiple'=>true, 'doNotShow'=>true),
            'sql'                   => "blob NULL",
        ),
        'alias' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['alias'],
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'alnum', 'doNotCopy'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
            'sql'                   => "varchar(128) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\ProductCallbacks', 'generateAlias'),
            ),
        ),
        'sku' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['sku'],
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'unique'=>true, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'general_legend', 'fe_sorting'=>true, 'fe_search'=>true),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['name'],
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
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['teaser'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:80px', 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'general_legend', 'multilingual'=>true),
            'sql'                   => "text NULL",
        ),
        'description' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['description'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'textarea',
            'eval'                  => array('mandatory'=>true, 'rte'=>'tinyMCE', 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'general_legend', 'multilingual'=>true, 'fe_search'=>true),
            'sql'                   => "text NULL",
        ),
        'description_meta' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['description_meta'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:60px'),
            'attributes'            => array('legend'=>'meta_legend', 'multilingual'=>true),
            'sql'                   =>  "text NULL",
        ),
        'keywords_meta' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['keywords_meta'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:40px'),
            'attributes'            => array('legend'=>'meta_legend', 'multilingual'=>true),
            'sql'                   => "text NULL",
        ),
        'price' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['price'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>13, 'rgxp'=>'price', 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'pricing_legend', 'fe_sorting'=>true, 'dynamic'=>true),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'prices' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['prices'],
            'inputType'             => 'dcaWizard',
            'foreignTable'          => 'tl_iso_prices',
            'eval'                  => array('tl_class'=>'clr'),
        ),
        'price_tiers' => array
        (
            'eval'                  => array('dynamic'=>true),
            'tableformat' => array
            (
                'min' => array
                (
                    'label'         => &$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min'],
                    'format'        => &$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min_format'],
                ),
                'price' => array
                (
                    'label'         => &$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['price'],
                    'rgxp'          => 'price'
                ),
                'tax_class' => array
                (
                    'doNotShow'     => true,
                ),
            ),
        ),
        'tax_class' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['tax_class'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => 'tl_iso_tax_class.name',
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'pricing_legend', 'dynamic'=>true),
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'baseprice' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['baseprice'],
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'foreignKey'            => 'tl_iso_baseprice.name',
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'pricing_legend'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'shipping_weight' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight'],
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'default'               => array('', 'kg'),
            'options'               => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'             => &$GLOBALS['TL_LANG']['WGT'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50 wizard', 'helpwizard'=>true),
            'attributes'            => array('legend'=>'shipping_legend'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'shipping_exempt' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'attributes'            => array('legend'=>'shipping_legend'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'images' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['images'],
            'exclude'               => true,
            'inputType'             => 'mediaManager',
            'explanation'           => 'mediaManager',
            'eval'                  => array('extensions'=>'jpeg,jpg,png,gif', 'helpwizard'=>true, 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'media_legend', 'fixed'=>true, 'multilingual'=>true, 'dynamic'=>true, 'fetch_fallback'=>true),
            'sql'                   => "blob NULL",
        ),
        'protected' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['protected'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'expert_legend'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'groups' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['groups'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'foreignKey'            => 'tl_member_group.name',
            'eval'                  => array('mandatory'=>true, 'multiple'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'guests' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['guests'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'attributes'            => array('legend'=>'expert_legend'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'cssID' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['cssID'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('multiple'=>true, 'size'=>2, 'tl_class'=>'w50'),
            'attributes'            => array('legend'=>'expert_legend'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'published' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['published'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('doNotCopy'=>true, 'tl_class'=>'clr'),
            'attributes'            => array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\Backend', 'truncateProductCache'),
            ),
        ),
        'start' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['start'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'attributes'            => array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'stop' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['stop'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'attributes'            => array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'source' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['source'],
            'eval'                  => array('mandatory'=>true, 'required'=>true, 'fieldType'=>'radio'),
        ),
        'variant_attributes' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_products']['variant_attributes'],
            'inputType'             => 'variantWizard',
            'options'               => array(),
            'eval'                  => array('doNotSaveEmpty'=>true),
        ),
    ),
);


/**
 * Adjust the data configuration array in variants view
 */
if (\Input::get('id'))
{
	$GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['new_product']['label'] = &$GLOBALS['TL_LANG']['tl_iso_products']['new_variant'];
	$GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['new_product']['href'] = 'act=create&mode=2&type=variant&pid=' . \Input::get('id') . '&gid=' . $this->Session->get('iso_products_gid');
}
