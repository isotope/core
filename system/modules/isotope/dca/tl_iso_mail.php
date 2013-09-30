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
 * Table tl_iso_mail
 */
$GLOBALS['TL_DCA']['tl_iso_mail'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'closed'                    => true,
        'switchToEdit'              => true,
        'ctable'                    => array('tl_iso_mail_content'),
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\tl_iso_mail', 'checkPermission'),
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
            'panelLayout'           => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                => array('name', 'sender'),
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['new'],
                'href'              => 'act=create',
                'class'             => 'header_new',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'importMail' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['importMail'],
                'href'              => 'key=importMail',
                'class'             => 'header_import_mail',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['edit'],
                'href'              => 'table=tl_iso_mail_content',
                'icon'              => 'edit.gif',
                'attributes'        => 'class="contextmenu"'
            ),
            'editheader' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['editheader'],
                'href'              => 'act=edit',
                'icon'              => 'header.gif',
                'attributes'        => 'class="edit-header"'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\tl_iso_mail', 'copyMail'),
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\tl_iso_mail', 'deleteMail'),
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            ),
            'exportMail' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['exportMail'],
                'href'              => 'key=exportMail',
                'icon'              => 'system/modules/isotope/assets/drive-download.png'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                   => '{name_legend},name;{address_legend},senderName,sender,cc,bcc;{expert_legend:hide},template,priority',
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
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_mail']['name'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
            'sql'                   =>  "varchar(255) NOT NULL default ''",
        ),
        'senderName' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_mail']['senderName'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'sender' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_mail']['sender'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'cc' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_mail']['cc'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'bcc' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_mail']['bcc'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'template' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_mail']['template'],
            'exclude'               => true,
            'inputType'             => 'select',
            'default'               => 'mail_default',
            'options'               => \Isotope\Backend::getTemplates('mail_'),
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'priority' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_mail']['priority'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array(1,2,3,4,5),
            'default'               => 3,
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "int(1) unsigned NOT NULL default '3'",
        ),
        'source' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_mail']['source'],
            'eval'                  => array('fieldType'=>'checkbox', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'imt', 'class'=>'mandatory')
        ),
    )
);
