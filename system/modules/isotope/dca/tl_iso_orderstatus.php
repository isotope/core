<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_orderstatus
 */
$GLOBALS['TL_DCA']['tl_iso_orderstatus'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'label'                     => &$GLOBALS['TL_LANG']['IMD']['orderstatus'][0],
        'enableVersioning'          => true,
        'closed'                    => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\Backend\OrderStatus\Callback', 'addDefault'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index',
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 5,
            'fields'                => array('name'),
            'panelLayout'           => 'filter;search,limit',
            'paste_button_callback' => array('Isotope\Backend\OrderStatus\Callback', 'pasteButton'),
            'icon'                  => 'system/modules/isotope/assets/images/traffic-light.png',
        ),
        'label' => array
        (
            'fields'                => array('name'),
            'format'                => '%s',
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['new'],
                'href'              => 'act=paste&amp;mode=create',
                'class'             => 'header_new',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['copy'],
                'href'              => 'act=paste&amp;mode=copy',
                'icon'              => 'copy.gif'
            ),
            'cut' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['cut'],
                'href'              => 'act=paste&amp;mode=cut',
                'icon'              => 'cut.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                   => '{name_legend},name,color,paid,welcomescreen;{email_legend},notification;{payment_legend:hide},saferpay_status',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'sorting' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'color' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['color'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('minlength'=>6, 'maxlength'=>6, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                   => "varchar(6) NOT NULL default ''"
        ),
        'paid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['paid'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'welcomescreen' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['welcomescreen'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'notification' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['notification'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => array('NotificationCenter\tl_module', 'getNotificationChoices'),
            'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'"
        ),
        'saferpay_status' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['saferpay_status'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('capture', 'cancel'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['saferpay_status'],
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(8) NOT NULL default ''"
        )
    )
);
