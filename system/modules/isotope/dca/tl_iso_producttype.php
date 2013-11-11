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
 */


/**
 * Table tl_iso_producttype
 */
$GLOBALS['TL_DCA']['tl_iso_producttype'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'closed'                    => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\Backend\ProductType\Callback', 'checkPermission'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 1,
            'fields'                => array('name'),
            'flag'                  => 1,
            'panelLayout'           => 'filter;search,limit',
        ),
        'label' => array
        (
            'fields'                => array('name', 'fallback'),
            'format'                => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'              => 'mod=&table=',
                'class'             => 'header_back',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_producttype']['new'],
                'href'              => 'act=create',
                'class'             => 'header_new',
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_producttype']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif',
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_producttype']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\Backend\ProductType\Callback', 'copyProductType')
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_producttype']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\ProductType\Callback', 'deleteProductType')
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_producttype']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('class', 'prices', 'variants'),
        'default'                   => '{name_legend},name,class',
        'standard'                  => '{name_legend},name,class,fallback;{description_legend:hide},description;{prices_legend:hide},prices;{template_legend},list_template,reader_template,list_gallery,reader_gallery;{attributes_legend},attributes;{variants_legend:hide},variants;{expert_legend:hide},shipping_exempt,downloads',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'prices'                    => 'show_price_tiers',
        'variants'                  => 'variant_attributes,force_variant_options',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['name'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'class' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['class'],
            'exclude'               => true,
            'inputType'             => 'select',
            'default'               => 'standard',
            'options'               => \Isotope\Model\Product::getModelTypeOptions(),
            'reference'             => &$GLOBALS['TL_LANG']['PRODUCT'],
            'eval'                  => array('mandatory'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'fallback' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['fallback'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('fallback'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'description' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['description'],
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:80px', 'tl_class'=>'clr'),
            'sql'                   => "text NULL",
        ),
        'prices' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['prices'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'clr'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'show_price_tiers' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['show_price_tiers'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'list_template' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['list_template'],
            'exclude'               => true,
            'inputType'             => 'select',
            'default'               => 'iso_list_default',
            'options_callback'      => function(\DataContainer $dc) {
                return \Isotope\Backend::getTemplates('iso_list_');
            },
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'reader_template' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['reader_template'],
            'exclude'               => true,
            'inputType'             => 'select',
            'default'               => 'iso_reader_default',
            'options_callback'      => function(\DataContainer $dc) {
                return \Isotope\Backend::getTemplates('iso_reader_');
            },
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'list_gallery' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['list_gallery'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'      		=> \Isotope\Model\Gallery::getTable().'.name',
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'reader_gallery' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['reader_gallery'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'      		=> \Isotope\Model\Gallery::getTable().'.name',
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'attributes' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes'],
            'exclude'               => true,
            'default' => array
            (
                array('name'=>'type', 'enabled'=>1),
                array('name'=>'pages', 'enabled'=>1),
                array('name'=>'alias', 'enabled'=>1),
                array('name'=>'sku', 'enabled'=>1),
                array('name'=>'name', 'enabled'=>1),
                array('name'=>'teaser', 'enabled'=>1),
                array('name'=>'description', 'enabled'=>1),
                array('name'=>'price', 'enabled'=>1),
                array('name'=>'images', 'enabled'=>1),
                array('name'=>'published', 'enabled'=>1),
                array('name'=>'start', 'enabled'=>1),
                array('name'=>'stop', 'enabled'=>1),
            ),
            'inputType'             => 'multiColumnWizard',
            'eval'                  => array
            (
                'tl_class'          =>'clr',
                'columnsCallback'   => array('Isotope\Backend\ProductType\AttributeWizard', 'getColumns'),
                'buttons'           => array('copy'=>false, 'delete'=>false),
            ),
            'sql'                   => 'blob NULL',
            'load_callback'         => array
            (
                array('Isotope\Backend\ProductType\AttributeWizard', 'load'),
            ),
            'save_callback'         => array
            (
                array('Isotope\Backend\ProductType\AttributeWizard', 'save'),
            ),
        ),
        'variants' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['variants'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'clr', 'submitOnChange'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'variant_attributes' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['variant_attributes'],
            'exclude'               => true,
            'inputType'             => 'multiColumnWizard',
            'eval'                  => array
            (
                'tl_class'          =>'clr',
                'columnsCallback'   => array('Isotope\Backend\ProductType\AttributeWizard', 'getColumns'),
                'buttons'           => array('copy'=>false, 'delete'=>false),
            ),
            'sql'                   => 'blob NULL',
            'load_callback'         => array
            (
                array('Isotope\Backend\ProductType\AttributeWizard', 'load'),
            ),
            'save_callback'         => array
            (
                array('Isotope\Backend\ProductType\AttributeWizard', 'save'),
            ),
        ),
        'force_variant_options' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['force_variant_options'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'clr'),
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'shipping_exempt' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['shipping_exempt'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'downloads' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['downloads'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''"
        ),
    )
);
