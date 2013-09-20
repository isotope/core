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
 * Table tl_iso_gallery
 */
$GLOBALS['TL_DCA']['tl_iso_gallery'] = array
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
            'flag'					=> 1,
            'fields'                => array('name'),
            'panelLayout'           => 'filter;search,limit',
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_gallery']['new'],
                'href'              => 'act=create',
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_gallery']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_gallery']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif'
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_gallery']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_gallery']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type', 'anchor'),
        'default'                   => '{name_legend},name,type',
        'standard'                  => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position',
        'standardlightbox'          => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size,lightbox_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position,lightbox_watermark_image,lightbox_watermark_position',
        'inline'                    => '{name_legend},name,type,placeholder;{size_legend},main_size,gallery_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['name'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'type' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['type'],
            'exclude'               => true,
            'filter'                => true,
            'default'               => 'standard',
            'inputType'             => 'select',
            'options'               => \Isotope\Model\Gallery::getModelTypeOptions(),
            'eval'                  => array('helpwizard'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'anchor' => array
        (
            'label'                 => $GLOBALS['TL_LANG']['tl_iso_gallery']['anchor'],
            'exclude'               => true,
            'inputType'             => 'radio',
            'options'               => array('none', 'reader', 'lightbox'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_gallery'],
            'eval'                  => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "varchar(8) NOT NULL default ''",
        ),
        'placeholder' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['placeholder'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'w50 w50h'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'main_size' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['main_size'],
            'exclude'               => true,
            'inputType'             => 'imageSize',
            'options'               => $GLOBALS['TL_CROP'],
            'reference'             => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'gallery_size' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_size'],
            'exclude'               => true,
            'inputType'             => 'imageSize',
            'options'               => $GLOBALS['TL_CROP'],
            'reference'             => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'lightbox_size' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_size'],
            'exclude'               => true,
            'inputType'             => 'imageSize',
            'options'               => $GLOBALS['TL_CROP'],
            'reference'             => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'main_watermark_image' => array
        (
            'label'                 => $GLOBALS['TL_LANG']['tl_iso_gallery']['main_watermark_image'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'main_watermark_position' => array
        (
            'label'                 => $GLOBALS['TL_LANG']['tl_iso_gallery']['main_watermark_position'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => $GLOBALS['TL_CROP']['crop'],
            'reference'             => $GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'gallery_watermark_image' => array
        (
            'label'                 => $GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_watermark_image'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'gallery_watermark_position' => array
        (
            'label'                 => $GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_watermark_position'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => $GLOBALS['TL_CROP']['crop'],
            'reference'             => $GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'lightbox_watermark_image' => array
        (
            'label'                 => $GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_watermark_image'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'lightbox_watermark_position' => array
        (
            'label'                 => $GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_watermark_position'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => $GLOBALS['TL_CROP']['crop'],
            'reference'             => $GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
    )
);
