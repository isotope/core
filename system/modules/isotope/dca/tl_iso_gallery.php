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
            array('Isotope\\Backend', 'initializeSetupModule'),
            array('Isotope\\Backend\\Gallery\\Callback', 'showJsLibraryHint'),
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
            'flag'                  => 1,
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
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
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
        'standard'                  => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position;{template_legend:hide},customTpl',
        'standardlightbox'          => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{lightbox_legend},lightbox_template,lightbox_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position,lightbox_watermark_image,lightbox_watermark_position;{template_legend:hide},customTpl',
        'inline'                    => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position;{template_legend:hide},customTpl',
        'inlinelightbox'            => '{name_legend},name,type,anchor,placeholder;{size_legend},main_size,gallery_size;{lightbox_legend},lightbox_template,lightbox_size;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position;{template_legend:hide},customTpl',
        'elevatezoom'               => '{name_legend},name,type,placeholder;{size_legend},main_size,gallery_size,zoom_size;{config_legend},zoom_windowSize,zoom_position,zoom_windowFade,zoom_border;{watermark_legend:hide},main_watermark_image,main_watermark_position,gallery_watermark_image,gallery_watermark_position,zoom_watermark_image,zoom_watermark_position;{template_legend:hide},customTpl',
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
            'options_callback'      => function() {
                return \Isotope\Model\Gallery::getModelTypeOptions();
            },
            'reference'             => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_gallery'],
            'eval'                  => array('helpwizard'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'anchor' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['anchor'],
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
            'sql'                   => "binary(16) NULL",
        ),
        'main_size' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['main_size'],
            'exclude'               => true,
            'inputType'             => 'imageSize',
            'options_callback'      => function () {
                return \Contao\System::getImageSizes();
            },
            'reference'             => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('includeBlankOption'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'gallery_size' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_size'],
            'exclude'               => true,
            'inputType'             => 'imageSize',
            'options_callback'      => function () {
                return \Contao\System::getImageSizes();
            },
            'reference'             => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('includeBlankOption'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'lightbox_template' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_template'],
            'exclude'               => true,
            'inputType'             => 'checkboxWizard',
            'options_callback'      => function() {
                // Do not use \Isotope\Backend::getTemplates() here, as they cannot be selected in a page layout!
                return array_merge(
                    \Contao\Controller::getTemplateGroup('moo_'),
                    \Contao\Controller::getTemplateGroup('j_')
                );
            },
            'eval'                  => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'w50 w50h'),
            'sql'                   => "blob NULL",
        ),
        'lightbox_size' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_size'],
            'exclude'               => true,
            'inputType'             => 'imageSize',
            'options_callback'      => function () {
                return \Contao\System::getImageSizes();
            },
            'reference'             => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('includeBlankOption'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'zoom_size' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_size'],
            'exclude'               => true,
            'inputType'             => 'imageSize',
            'options_callback'      => function () {
                return \Contao\System::getImageSizes();
            },
            'reference'             => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('includeBlankOption'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'zoom_windowSize' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_windowSize'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('multiple'=>true, 'size'=>2, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'zoom_position' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position'],
            'exclude'               => true,
            'inputType'             => 'multiColumnWizard',
            'eval'                  => array(
                'rgxp' => 'digit',
                'helpwizard' => true,
                'tl_class' => 'w50',
                'disableSorting' => true,
                'minCount' => 1,
                'maxCount' => 1,
                'hideButtons' => true,
                'columnFields' => array(
                    2 => array(
                        'inputType' => 'select',
                        'options'   => array('pos1', 'pos2', 'pos3', 'pos4', 'pos5', 'pos6', 'pos7', 'pos8', 'pos9', 'pos10', 'pos11', 'pos12', 'pos13', 'pos14', 'pos15', 'pos16',),
                        'reference' => &$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position'],
                        'eval'      => array('hideHead' => true, 'class' => 'tl_select_interval'),
                    ),
                    0 => array(
                        'inputType' => 'text',
                        'eval'      => array('hideHead' => true, 'rgxp' => 'digit'),
                    ),
                    1 => array(
                        'inputType' => 'text',
                        'eval'      => array('hideHead' => true, 'rgxp' => 'digit'),
                    ),
                ),
            ),
            'sql'                   => "varchar(64) NOT NULL default ''",
            'explanation'           => 'elevatezoom_position',
            'load_callback' => [function ($value) {
                $value = \Contao\StringUtil::deserialize($value);
                return \is_array($value) && !\is_array($value[0]) ? [$value] : [];
            }],
            'save_callback' => [function ($value) {
                $value = \Contao\StringUtil::deserialize($value);
                return \is_array($value) ? serialize($value[0]) : '';
            }],
        ),
        'zoom_windowFade' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_windowFade'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('multiple'=>true, 'size'=>2, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'zoom_border' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_border'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>6, 'multiple'=>true, 'size'=>2, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'main_watermark_image' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['main_watermark_image'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "binary(16) NULL",
        ),
        'main_watermark_position' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['main_watermark_position'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('left_top', 'center_top', 'right_top', 'left_center', 'center_center', 'right_center', 'left_bottom', 'center_bottom', 'right_bottom'),
            'reference'             => $GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'gallery_watermark_image' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_watermark_image'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "binary(16) NULL",
        ),
        'gallery_watermark_position' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_watermark_position'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('left_top', 'center_top', 'right_top', 'left_center', 'center_center', 'right_center', 'left_bottom', 'center_bottom', 'right_bottom'),
            'reference'             => $GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'lightbox_watermark_image' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_watermark_image'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "binary(16) NULL",
        ),
        'lightbox_watermark_position' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_watermark_position'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('left_top', 'center_top', 'right_top', 'left_center', 'center_center', 'right_center', 'left_bottom', 'center_bottom', 'right_bottom'),
            'reference'             => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'zoom_watermark_image' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_watermark_image'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes'], 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "binary(16) NULL",
        ),
        'zoom_watermark_position' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_watermark_position'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('left_top', 'center_top', 'right_top', 'left_center', 'center_center', 'right_center', 'left_bottom', 'center_bottom', 'right_bottom'),
            'reference'             => $GLOBALS['TL_LANG']['MSC'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'customTpl' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_gallery']['customTpl'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \Isotope\Backend::getTemplates('iso_gallery_');
            },
            'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
    )
);
