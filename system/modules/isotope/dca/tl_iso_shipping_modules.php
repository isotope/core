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
 * Load tl_iso_products data container and language files
 */
$this->loadDataContainer('tl_iso_products');
$this->loadLanguageFile('tl_iso_products');


/**
 * Table tl_iso_shipping_modules
 */
$GLOBALS['TL_DCA']['tl_iso_shipping_modules'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ctable'                      => array('tl_iso_shipping_options'),
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'closed'					  => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\tl_iso_shipping_modules', 'checkPermission'),
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('name'),
            'flag'                    => 1,
            'panelLayout'             => 'sort,filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('name', 'type'),
            'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            'label_callback'		  => array('Isotope\Backend', 'addPublishIcon'),
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
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new'],
                'href'					=> 'act=create',
                'class'					=> 'header_new',
                'attributes'			=> 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif',
                'button_callback'     => array('Isotope\tl_iso_shipping_modules', 'copyShippingModule'),
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'     => array('Isotope\tl_iso_shipping_modules', 'deleteShippingModule'),
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            ),
            'options' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['options'],
                'href'                => 'table=tl_iso_shipping_options',
                'icon'                => 'tablewizard.gif',
                'button_callback'     => array('Isotope\tl_iso_shipping_modules', 'optionsButton'),
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'					=> array('type', 'protected'),
        'default'						=> '{title_legend},name,label,type',
        'Flat'							=> '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation,surcharge_field;{config_legend},countries,subdivisions,postalCodes,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'OrderTotal'					=> '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class;{config_legend},countries,subdivisions,postalCodes,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'WeightTotal'					=> '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},tax_class;{config_legend},weight_unit,countries,subdivisions,postalCodes,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'UPS'							=> '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},tax_class;{ups_legend},ups_enabledService,ups_accessKey,ups_userName,ups_password;{config_legend},weight_unit,countries,subdivisions,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'USPS'							=> '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},tax_class;{usps_legend},usps_enabledService,usps_userName;{config_legend},countries,subdivisions,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled'
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'protected'						=> 'groups',
    ),

    // Fields
    'fields' => array
    (
        'name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
        ),
        'label' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['type'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'default'				  => 'Flat',
            'options'                 => array_keys(\Isotope\Factory\Shipping::getClasses()),
            'reference'               => \Isotope\Factory\Shipping::getLabels(),
            'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50')
        ),
        'note' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'decodeEntities'=>true),
        ),
        'ups_enabledService' => array
        (
            'label'					  => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'],
            'exclude'				  => true,
            'inputType'				  => 'select',
            'options'				  => $GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service'],
            'eval'					  => array('mandatory'=>true)
        ),
        'ups_accessKey' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
        ),
        'ups_userName' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
        ),
        'ups_password' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
        ),
        'usps_enabledService' => array
        (
            'label'					  => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'],
            'exclude'				  => true,
            'inputType'				  => 'select',
            'options'				  => $GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service'],
            'eval'					  => array('mandatory'=>true)
        ),
        'usps_userName' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
        ),
        'countries' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => $this->getCountries(),
            'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true)
        ),
        'subdivisions' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'],
            'exclude'                 => true,
            'sorting'                 => true,
            'inputType'               => 'conditionalselect',
            'options_callback'		  => array('Isotope\Backend', 'getSubdivisions'),
            'eval'                    => array('multiple'=>true, 'size'=>8, 'conditionField'=>'countries', 'tl_class'=>'w50 w50h'),
        ),
        'postalCodes' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['postalCodes'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('style'=>'height:40px', 'tl_class'=>'clr'),
        ),
        'minimum_total' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
        ),
        'maximum_total' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
        ),
        'product_types' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'foreignKey'			  => 'tl_iso_producttypes.name',
            'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr'),
        ),
        'price' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>16, 'rgxp'=>'surcharge', 'tl_class'=>'w50'),
        ),
        'tax_class' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options'                 => \Isotope\Backend::getTaxClassesWithSplit(),
            'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
        ),
        'flatCalculation' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('flat', 'perProduct', 'perItem'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules'],
            'eval'                    => array('tl_class'=>'w50'),
        ),
        'surcharge_field' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array_keys($GLOBALS['TL_DCA']['tl_iso_products']['fields']),
            'reference'               => &$GLOBALS['TL_LANG']['tl_iso_products'],
            'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
        ),
        'weight_unit' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit'],
            'exclude'                 => true,
            'default'				  => 'kg',
            'inputType'               => 'select',
            'options'				  => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'				  => &$GLOBALS['ISO_LANG']['WGT'],
            'eval'                    => array('tl_class'=>'clr', 'helpwizard'=>&$GLOBALS['ISO_LANG']['WGT']),
        ),
        'guests' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
        ),
        'protected' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>true)
        ),
        'groups' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_member_group.name',
            'eval'                    => array('multiple'=>true)
        ),
        'enabled' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
        ),
    )
);
