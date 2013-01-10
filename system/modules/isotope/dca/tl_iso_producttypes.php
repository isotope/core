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
 * Table tl_iso_producttypes
 */
$GLOBALS['TL_DCA']['tl_iso_producttypes'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'				=> 'Table',
        'enableVersioning'			=> true,
        'closed'					=> true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\tl_iso_producttypes', 'checkPermission'),
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'					=> 1,
            'fields'				=> array('name'),
            'flag'					=> 1,
            'panelLayout'			=> 'filter;search,limit',
        ),
        'label' => array
        (
            'fields'				=> array('name', 'fallback'),
            'format'				=> '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'					=> 'mod=&table=',
                'class'					=> 'header_back',
                'attributes'			=> 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['new'],
                'href'					=> 'act=create',
                'class'					=> 'header_new',
                'attributes'			=> 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'				=> 'act=select',
                'class'				=> 'header_edit_all',
                'attributes'		=> 'onclick="Backend.getScrollOffset();"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['edit'],
                'href'				=> 'act=edit',
                'icon'				=> 'edit.gif',
            ),
            'copy' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['copy'],
                'href'				=> 'act=copy',
                'icon'				=> 'copy.gif',
                'button_callback'     => array('Isotope\tl_iso_producttypes', 'copyProductType')
            ),
            'delete' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['delete'],
                'href'				=> 'act=delete',
                'icon'				=> 'delete.gif',
                'attributes'		=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'     => array('Isotope\tl_iso_producttypes', 'deleteProductType')
            ),
            'show' => array
            (
                'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['show'],
                'href'				=> 'act=show',
                'icon'				=> 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'				=> array('class', 'prices', 'variants'),
        'default'					=> '{name_legend},name,class',
        'standard'					=> '{name_legend},name,class,fallback;{description_legend:hide},description;{prices_legend:hide},prices;{template_legend},list_template,reader_template;{attributes_legend},attributes;{variants_legend:hide},variants;{expert_legend:hide},shipping_exempt,downloads',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'prices'					=> 'show_price_tiers',
        'variants'					=> 'variant_attributes,force_variant_options',
    ),

    // Fields
    'fields' => array
    (
        'name' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['name'],
            'exclude'				=> true,
            'inputType'				=> 'text',
            'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
        ),
        'class' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['class'],
            'exclude'				=> true,
            'inputType'				=> 'select',
            'default'				=> 'standard',
            'options'				=> array_keys($GLOBALS['ISO_PRODUCT']),
            'reference'				=> &$GLOBALS['ISO_LANG']['PRODUCT'],
            'eval'					=> array('mandatory'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
        ),
        'fallback' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['fallback'],
            'exclude'				=> true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('fallback'=>true, 'tl_class'=>'w50'),
        ),
        'description' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['description'],
            'exclude'				=> true,
            'inputType'				=> 'textarea',
            'eval'					=> array('style'=>'height:80px', 'tl_class'=>'clr'),
        ),
        'prices' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['prices'],
            'exclude'				=> true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
        ),
        'show_price_tiers' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['show_price_tiers'],
            'exclude'				=> true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('tl_class'=>'w50'),
        ),
        'list_template' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['list_template'],
            'exclude'                 => true,
            'inputType'				=> 'select',
            'default'				=> 'iso_list_default',
            'options_callback'		=> array('Isotope\tl_iso_producttypes', 'getListTemplates'),
            'eval'					=> array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
        ),
        'reader_template' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['reader_template'],
            'exclude'                 => true,
            'inputType'				=> 'select',
            'default'				=> 'iso_reader_default',
            'options_callback'		=> array('Isotope\tl_iso_producttypes', 'getReaderTemplates'),
            'eval'					=> array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
        ),
        'attributes' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes'],
            'exclude'				=> true,
            'inputType'				=> 'attributeWizard',
            'default'				=> array
            (
                'type'				=> array('enabled'=>1, 'position'=>1),
                'pages'				=> array('enabled'=>1, 'position'=>2),
                'alias'				=> array('enabled'=>1, 'position'=>3),
                'sku'				=> array('enabled'=>1, 'position'=>4),
                'name'				=> array('enabled'=>1, 'position'=>5),
                'teaser'			=> array('enabled'=>1, 'position'=>6),
                'description'		=> array('enabled'=>1, 'position'=>7),
                'price'				=> array('enabled'=>1, 'position'=>8),
                'tax_class'			=> array('enabled'=>1, 'position'=>9),
                'images'			=> array('enabled'=>1, 'position'=>10),
                'published'			=> array('enabled'=>1, 'position'=>11),
            ),
            'eval'					=> array('helpwizard'=>true, 'tl_class'=>'clr', 'tl_classes'=>array('clr', 'clr long', 'long', 'w50', 'w50 m12')),
        ),
        'variants' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['variants'],
            'exclude'				=> true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('tl_class'=>'clr', 'submitOnChange'=>true),
        ),
        'variant_attributes' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['variant_attributes'],
            'exclude'				=> true,
            'inputType'				=> 'attributeWizard',
            'eval'					=> array('helpwizard'=>true, 'variants'=>true, 'tl_class'=>'clr', 'tl_classes'=>array('clr', 'clr long', 'long', 'w50', 'w50 m12')),
        ),
        'force_variant_options' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['force_variant_options'],
            'exclude'				=> true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('tl_class'=>'clr'),
        ),
        'shipping_exempt' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['shipping_exempt'],
            'exclude'				=> true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('tl_class'=>'w50'),
        ),
        'downloads' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['downloads'],
            'exclude'				=> true,
            'inputType'				=> 'checkbox',
            'eval'					=> array('tl_class'=>'w50'),
        ),
    )
);
