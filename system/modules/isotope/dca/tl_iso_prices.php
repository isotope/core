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
 * Table tl_iso_prices
 */
$GLOBALS['TL_DCA']['tl_iso_prices'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'					=> 'Table',
        'enableVersioning'				=> true,
        'ptable'						=> 'tl_iso_products',
        'ctable'						=> array('tl_iso_price_tiers'),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'						=> 4,
            'fields'					=> array('id'),
            'flag'						=> 1,
            'panelLayout'				=> 'filter;search,limit',
            'headerFields'				=> array('id', 'name', 'alias', 'sku'),
            'disableGrouping'			=> true,
            'child_record_callback'		=> array('Isotope\tl_iso_prices', 'listRows')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'					=> 'act=select',
                'class'					=> 'header_edit_all',
                'attributes'			=> 'onclick="Backend.getScrollOffset();"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['edit'],
                'href'					=> 'act=edit',
                'icon'					=> 'edit.gif'
            ),
            'copy' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['copy'],
                'href'					=> 'act=copy',
                'icon'					=> 'copy.gif'
            ),
            'delete' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['delete'],
                'href'					=> 'act=delete',
                'icon'					=> 'delete.gif',
                'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['show'],
                'href'					=> 'act=show',
                'icon'					=> 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'						=> '{price_legend},price_tiers,tax_class;{limit_legend},config_id,member_group,start,stop',
        'dcawizard'						=> 'price_tiers,tax_class,config_id,member_group,start,stop',
    ),

    // Fields
    'fields' => array
    (
        'price_tiers' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['price_tiers'],
            'exclude'               => true,
            'inputType'				=> 'multiColumnWizard',
            'eval'					=> array
            (
                'doNotSaveEmpty'	=> true,
                'tl_class'			=> 'clr',
                'disableSorting'	=> true,
                'columnFields'		=> array
                (
                    'min' => array
                    (
                        'label'		=> &$GLOBALS['TL_LANG']['tl_iso_prices']['price_tier_columns']['min'],
                        'inputType'	=> 'text',
                        'eval'		=> array('mandatory'=>true, 'rgxp'=>'digit', 'style'=>'width:100px'),
                    ),
                    'price' => array
                    (
                        'label'		=> &$GLOBALS['TL_LANG']['tl_iso_prices']['price_tier_columns']['price'],
                        'inputType'	=> 'text',
                        'eval'		=> array('mandatory'=>true, 'rgxp'=>'price', 'style'=>'width:100px'),
                    ),
                ),
            ),
            'load_callback' => array
            (
                array('Isotope\tl_iso_prices', 'loadTiers'),
            ),
            'save_callback' => array
            (
                array('Isotope\tl_iso_prices', 'saveTiers'),
            ),
        ),
        'tax_class' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['tax_class'],
            'exclude'               => true,
            'inputType'				=> 'select',
            'default'				=> &$GLOBALS['TL_DCA']['tl_iso_products']['fields']['tax_class']['default'],
            'foreignKey'			=> 'tl_iso_tax_class.name',
            'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'clr'),
        ),
        'config_id' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_prices']['config_id'],
            'exclude'               => true,
            'inputType'               => 'select',
            'foreignKey'			  => 'tl_iso_config.name',
            'eval'					  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
        ),
        'member_group' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['member_group'],
            'exclude'               => true,
            'inputType'				=> 'select',
            'foreignKey'			=> 'tl_member_group.name',
            'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
        ),
        'start' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['start'],
            'exclude'               => true,
            'inputType'				=> 'text',
            'eval'					=> array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
        ),
        'stop' => array
        (
            'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['stop'],
            'exclude'               => true,
            'inputType'				=> 'text',
            'eval'					=> array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
        ),
    )
);
