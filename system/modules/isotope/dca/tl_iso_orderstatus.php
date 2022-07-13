<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
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
        'backlink'                  => 'do=iso_setup',
        'onload_callback' => array
        (
            array('Isotope\Backend\OrderStatus\Callback', 'addDefault'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index',
                'paid' => 'index',
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
            'label_callback'        => array('Isotope\Backend\OrderStatus\Callback', 'getColoredLabel'),
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'href'              => 'act=paste&amp;mode=copy',
                'icon'              => 'copy.gif'
            ),
            'cut' => array
            (
                'href'              => 'act=paste&amp;mode=cut',
                'icon'              => 'cut.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
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
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'color' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('minlength'=>6, 'maxlength'=>6, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                   => "varchar(6) NOT NULL default ''"
        ),
        'paid' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'welcomescreen' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'notification' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => array('Isotope\Backend\OrderStatus\Callback', 'getNotificationChoices'),
            'eval'                  => array('multiple'=>true, 'csv'=>',', 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'saferpay_status' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('capture', 'cancel'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['saferpay_status'],
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(8) NOT NULL default ''"
        )
    )
);
