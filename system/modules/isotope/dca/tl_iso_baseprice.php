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
 */


/**
 * Table tl_iso_baseprice
 */
$GLOBALS['TL_DCA']['tl_iso_baseprice'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'					=> 'Table',
        'enableVersioning'				=> true,
        'closed'					=> true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'						=> 1,
            'fields'					=> array('name'),
            'flag'						=> 1,
            'panelLayout'				=> 'search,limit',
        ),
        'label' => array
        (
            'fields'					=> array('name'),
            'format'					=> '%s',
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'                => 'mod=&table=',
                'class'               => 'header_back',
                'attributes'          => 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_baseprice']['new'],
                'href'                => 'act=create',
                'class'               => 'header_new',
                'attributes'          => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'					=> 'act=select',
                'class'					=> 'header_edit_all',
                'attributes'			=> 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_baseprice']['edit'],
                'href'					=> 'act=edit',
                'icon'					=> 'edit.gif'
            ),
            'copy' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_baseprice']['copy'],
                'href'					=> 'act=copy',
                'icon'					=> 'copy.gif'
            ),
            'delete' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_baseprice']['delete'],
                'href'					=> 'act=delete',
                'icon'					=> 'delete.gif',
                'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'					=> &$GLOBALS['TL_LANG']['tl_iso_baseprice']['show'],
                'href'					=> 'act=show',
                'icon'					=> 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'						=> '{name_legend},name,amount,label',
    ),

    // Fields
    'fields' => array
    (
        'name' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_baseprice']['name'],
            'exclude'					=> true,
            'search'					=> true,
            'inputType'					=> 'text',
            'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'amount' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_baseprice']['amount'],
            'exclude'					=> true,
            'inputType'					=> 'text',
            'eval'						=> array('mandatory'=>true, 'rgxp'=>'digit', 'maxlength'=>32, 'tl_class'=>'w50'),
        ),
        'label' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_baseprice']['label'],
            'exclude'					=> true,
            'search'					=> true,
            'inputType'					=> 'text',
            'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
        ),
    )
);
