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
 * Table tl_iso_mail_content
 */
$GLOBALS['TL_DCA']['tl_iso_mail_content'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ptable'                      => 'tl_iso_mail',
        'enableVersioning'            => true,
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 4,
            'fields'                  => array('language'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit',
            'headerFields'            => array('name', 'senderName', 'sender'),
            'disableGrouping'          => true,
            'child_record_callback'   => array('Isotope\tl_iso_mail_content', 'listRows'),
        ),
        'label' => array
        (
            'fields'                  => array('subject', 'language'),
            'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ),
        'global_operations' => array
        (
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
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'    => array('textOnly'),
        'default'        => '{settings_legend},language,fallback;{content_legend},subject,html,text,textOnly,attachments',
        'textOnly'        => '{settings_legend},language,fallback;{content_legend},subject,text,textOnly,attachments'
    ),

    // Fields
    'fields' => array
    (
        'pid' => array
        (
            'foreignKey'              => 'tl_iso_mail.name',
            'relation'                => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'language' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['language'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => $GLOBALS['TL_LANGUAGE'],
            'options'                 => $this->getLanguages(),
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
        ),
        'fallback' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['fallback'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
        ),
        'subject' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['subject'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'long'),
        ),
        'text' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['text'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true, 'helpwizard'=>true),
            'explanation'             => 'isoMailTokens',
        ),
        'textOnly' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['textOnly'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>true),
        ),
        'html' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['html'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory'=>true, 'rte'=>'tinyMCE', 'decodeEntities'=>true, 'helpwizard'=>true),
            'explanation'             => 'isoMailTokens',
        ),
        'attachments' => array
        (
              'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['attachments'],
            'exclude'                 => true,
              'inputType'               => 'fileTree',
              'eval'                       => array('mandatory'=>false, 'files'=>true, 'filesOnly'=>true,'fieldType' => 'checkbox'),
        ),
    )
);
