<?php

use Contao\Controller;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Isotope\Backend;
use Isotope\CoreBundle\Gallery\AnchorTarget;

$GLOBALS['TL_DCA']['tl_iso_gallery'] = [
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
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => ['name'],
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'type'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'anchor'],
        'default' => '{name_legend},name,type',
        'standard' => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{template_legend:hide},customTpl',
        'standardlightbox' => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{lightbox_legend},lightbox_template,lightbox_size;{template_legend:hide},customTpl',
        'inline' => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{template_legend:hide},customTpl',
        'inlinelightbox' => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{lightbox_legend},lightbox_template,lightbox_size;{template_legend:hide},customTpl',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'filter' => true,
            'default' => 'standard',
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_gallery'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'anchor' => [
            'inputType' => 'radio',
            'enum' => AnchorTarget::class,
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'clr w50 w50h'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'placeholder' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'w50 w50h'],
            'sql' => 'binary(16) NULL',
        ],
        'main_size' => [
            'inputType' => 'imageSize',
            'options_callback' => static fn () => System::getContainer()->get('contao.image.sizes')->getAllOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'gallery_size' => [
            'inputType' => 'imageSize',
            'options_callback' => static fn () => System::getContainer()->get('contao.image.sizes')->getAllOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'lightbox_template' => [
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
            'inputType' => 'imageSize',
            'options_callback' => static fn () => System::getContainer()->get('contao.image.sizes')->getAllOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['includeBlankOption' => true, 'rgxp' => 'digit', 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'customTpl' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) COLLATE ascii_bin NOT NULL default ''"
        ],
    ],
];
