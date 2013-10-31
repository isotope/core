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
\System::loadLanguageFile('tl_iso_products');


/**
 * Table tl_iso_shipping
 */
$GLOBALS['TL_DCA']['tl_iso_shipping'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'switchToEdit'              => true,
        'enableVersioning'          => true,
        'closed'                    => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\tl_iso_shipping', 'checkPermission'),
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
            'panelLayout'           => 'sort,filter;search,limit'
        ),
        'label' => array
        (
            'fields'                => array('name', 'type'),
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['new'],
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
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\tl_iso_shipping', 'copyShippingModule'),
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\tl_iso_shipping', 'deleteShippingModule'),
            ),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('Isotope\tl_iso_shipping', 'toggleIcon')
			),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type', 'protected'),
        'default'                   => '{title_legend},name,label,type',
        'flat'                      => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation;{config_legend},countries,subdivisions,postalCodes,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
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
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['name'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'label' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['label'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'type' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['type'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'default'               => 'flat',
            'options'               => \Isotope\Model\Shipping::getModelTypeOptions(),
            'eval'                  => array('helpwizard'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'note' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['note'],
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('rte'=>'tinyMCE', 'decodeEntities'=>true),
            'sql'                   => "text NULL",
        ),
        'countries' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['countries'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => \System::getCountries(),
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
        ),
        'subdivisions' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['subdivisions'],
            'exclude'               => true,
            'sorting'               => true,
            'inputType'             => 'conditionalselect',
            'options_callback'      => array('Isotope\Backend', 'getSubdivisions'),
            'eval'                  => array('multiple'=>true, 'size'=>8, 'conditionField'=>'countries', 'tl_class'=>'w50 w50h'),
            'sql'                   => "longblob NULL",
        ),
        'postalCodes' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['postalCodes'],
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:40px', 'tl_class'=>'clr'),
            'sql'                   => "text NULL",
        ),
        'minimum_total' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['minimum_total'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'maximum_total' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['maximum_total'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'product_types' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['product_types'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\ProductType::getTable().'.name',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr'),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'price' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['price'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>16, 'rgxp'=>'surcharge', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'tax_class' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['tax_class'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\TaxClass::getTable().'.name',
            'options_callback'      => array('\Isotope\Model\TaxClass', 'getOptionsWithSplit'),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'flatCalculation' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['flatCalculation'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('flat', 'perProduct', 'perItem'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'guests' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['guests'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'protected' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['protected'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'groups' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['groups'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'foreignKey'            => 'tl_member_group.name',
            'eval'                  => array('multiple'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'enabled' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_shipping']['enabled'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
    )
);
