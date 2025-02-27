<?php

use Contao\Controller;
use Contao\DC_Table;
use Contao\System;
use Isotope\Backend;
use Isotope\Model\Gallery;

/*
 * Table tl_iso_gallery
 */
$GLOBALS['TL_DCA']['tl_iso_gallery'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'flag' => 1,
            'fields' => ['name'],
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'type'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\')) return false; Backend.getScrollOffset();"',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type', 'anchor'],
        'default' => '{name_legend},name,type',
        'standard' => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position;{template_legend:hide},customTpl',
        'standardlightbox' => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{lightbox_legend},lightbox_template,lightbox_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position,lightbox_watermark_image,lightbox_watermark_position;{template_legend:hide},customTpl',
        'inline' => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position;{template_legend:hide},customTpl',
        'inlinelightbox' => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{lightbox_legend},lightbox_template,lightbox_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position;{template_legend:hide},customTpl',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'exclude' => true,
            'filter' => true,
            'default' => 'standard',
            'inputType' => 'select',
            'options_callback' => static fn () => Gallery::getModelTypeOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_gallery'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'anchor' => [
            'exclude' => true,
            'inputType' => 'radio',
            'options' => ['none', 'reader', 'lightbox'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_gallery'],
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'clr w50 w50h'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'placeholder' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'w50 w50h'],
            'sql' => 'binary(16) NULL',
        ],
        'main_size' => [
            'exclude' => true,
            'inputType' => 'imageSize',
            'options_callback' => static fn () => System::getContainer()->get('contao.image.sizes')->getAllOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'gallery_size' => [
            'exclude' => true,
            'inputType' => 'imageSize',
            'options_callback' => static fn () => System::getContainer()->get('contao.image.sizes')->getAllOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'lightbox_template' => [
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'options_callback' => static fn () =>
                // Do not use \Isotope\Backend::getTemplates() here, as they cannot be selected in a page layout!
                array_merge(
                    Controller::getTemplateGroup('moo_'),
                    Controller::getTemplateGroup('j_'),
                    Controller::getTemplateGroup('js_'),
                ),
            'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'w50 w50h'],
            'sql' => 'blob NULL',
        ],
        'lightbox_size' => [
            'exclude' => true,
            'inputType' => 'imageSize',
            'options_callback' => static fn () => System::getContainer()->get('contao.image.sizes')->getAllOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'main_watermark_image' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr w50 w50h'],
            'sql' => 'binary(16) NULL',
        ],
        'main_watermark_position' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['left_top', 'center_top', 'right_top', 'left_center', 'center_center', 'right_center', 'left_bottom', 'center_bottom', 'right_bottom'],
            'reference' => $GLOBALS['TL_LANG']['MSC'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'gallery_watermark_image' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr w50 w50h'],
            'sql' => 'binary(16) NULL',
        ],
        'gallery_watermark_position' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['left_top', 'center_top', 'right_top', 'left_center', 'center_center', 'right_center', 'left_bottom', 'center_bottom', 'right_bottom'],
            'reference' => $GLOBALS['TL_LANG']['MSC'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'lightbox_watermark_image' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr w50 w50h'],
            'sql' => 'binary(16) NULL',
        ],
        'lightbox_watermark_position' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['left_top', 'center_top', 'right_top', 'left_center', 'center_center', 'right_center', 'left_bottom', 'center_bottom', 'right_bottom'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'customTpl' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => Backend::getTemplates('iso_gallery_'),
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];
