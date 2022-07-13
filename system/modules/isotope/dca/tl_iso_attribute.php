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
 * Load tl_iso_product language file for field legends
 */
\System::loadLanguageFile('tl_iso_product');


/**
 * Table tl_iso_attribute
 */
$GLOBALS['TL_DCA']['tl_iso_attribute'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'closed'                    => true,
        'ctable'                    => array(\Isotope\Model\AttributeOption::getTable()),
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\Backend\Attribute\Callback', 'onLoad'),
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\Backend\Attribute\DatabaseUpdate', 'onSubmit'),
        ),
        'ondelete_callback' => array
        (
            array('Isotope\Backend\Attribute\DatabaseUpdate', 'onDelete'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'    => 'primary',
                'type'  => 'index'
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 1,
            'fields'                => array('legend', 'name'),
            'flag'                  => 1,
            'panelLayout'           => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                => array('name', 'field_name', 'type'),
            'format'                => '<div style="float:left; width:200px">%s</div><div style="float:left; width:200px; color:#b3b3b3;">%s</div><div style="color:#b3b3b3">%s</div>'
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_attribute']['new'],
                'href'              => 'act=create',
                'class'             => 'header_new',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            )
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
                'href'              => 'act=copy',
                'icon'              => 'copy.gif'
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['tl_iso_attribute']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type', 'optionsSource', 'includeBlankOption', 'variant_option', 'multiple', 'storeFile', 'files', 'rgxp', 'checkoutRelocate', 'chunking'),
        'default'                   => '{attribute_legend},name,field_name,type,legend',
        'text'                      => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_sorting,be_search,be_filter',
        'textdigit'                 => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minval,maxval,step,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_sorting,be_search,be_filter',
        'textarea'                  => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,rte,mandatory,multilingual;{search_filters_legend},fe_search,fe_sorting,be_search',
        'select'                    => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'selectproduct'             => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple;{search_filters_legend},fe_sorting',
        'selectvariant_option'      => '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},optionsSource,blankOptionLabel;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'radio'                     => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'radioproduct'              => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory;{search_filters_legend},fe_sorting',
        'radiovariant_option'       => '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'checkbox'                  => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'checkboxproduct'           => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory;{search_filters_legend},fe_sorting',
        'conditionalselect'         => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple,conditionField;{search_filters_legend},fe_filter,fe_sorting',
        'mediaManager'              => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},extensions,mandatory',
        'fileTree'                  => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,sortBy,path,mandatory,multilingual,files,isGallery',
        'pageTree'                  => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,mandatory,multilingual,rootNodes',
        'downloads'                 => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,sortBy,path,mandatory,multilingual,files,isGallery',
        'upload'                    => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},extensions,maxlength,mandatory;{store_legend:hide},checkoutRelocate',
        'media'                     => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},path,mandatory,multilingual',
        'quantitySurcharge'         => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},minval,maxval,step,placeholder,mandatory',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'storeFile'                 => 'uploadFolder,useHomeDir,doNotOverwrite',
        'files'                     => 'extensions,filesOnly',
        'optionsSource_attribute'   => 'options',
        'optionsSource_table'       => 'optionsTable',
        'optionsSource_foreignKey'  => 'foreignKey',
        'multiple'                  => 'size',
        'includeBlankOption'        => 'blankOptionLabel',
        'checkoutRelocate'          => 'checkoutTargetFolder,checkoutTargetFile',
        'chunking'                  => 'chunkSize',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'field_name' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>30, 'unique'=>true, 'doNotCopy'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(30) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\Backend\Attribute\Callback', 'validateFieldName'),
            ),
        ),
        'type' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \Isotope\Model\Attribute::getModelTypeOptions();
            },
            'reference'             => &$GLOBALS['TL_LANG']['ATTR'],
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'legend' => array
        (
            'exclude'               => true,
            'search'                => true,
            'default'               => 'options_legend',
            'inputType'             => 'select',
            'options'               => array('general_legend', 'meta_legend', 'pricing_legend', 'inventory_legend', 'shipping_legend', 'options_legend', 'media_legend', 'expert_legend', 'publish_legend'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_product'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'description' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'optionsSource' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'radio',
            'options_callback'      => function($dc) {
                $arrOptions = [
                    \Isotope\Interfaces\IsotopeAttributeWithOptions::SOURCE_TABLE,
                    \Isotope\Interfaces\IsotopeAttributeWithOptions::SOURCE_FOREIGNKEY,
                    \Isotope\Interfaces\IsotopeAttributeWithOptions::SOURCE_ATTRIBUTE,
                ];

                if (!$dc->activeRecord->variant_option && $dc->activeRecord->customer_defined) {
                    $arrOptions = [
                        \Isotope\Interfaces\IsotopeAttributeWithOptions::SOURCE_TABLE,
                        \Isotope\Interfaces\IsotopeAttributeWithOptions::SOURCE_PRODUCT,
                        \Isotope\Interfaces\IsotopeAttributeWithOptions::SOURCE_FOREIGNKEY,
                        \Isotope\Interfaces\IsotopeAttributeWithOptions::SOURCE_ATTRIBUTE,
                    ];
                }

                if ('checkbox' === $dc->activeRecord->type && !$dc->activeRecord->customer_defined) {
                    array_unshift($arrOptions, \Isotope\Interfaces\IsotopeAttributeWithOptions::SOURCE_NAME);
                }

                return $arrOptions;
            },
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsSource'],
            'eval'                  => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'clr'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'options' => array
        (
            'exclude'               => true,
            'inputType'             => 'multiColumnWizard',
            'eval' => array
            (
                'mandatory'         => true,
                'dragAndDrop'       => true,
                'tl_class'          => 'clr',
                'columnsCallback'   => array('Isotope\Backend\Attribute\OptionsWizard', 'getColumns'),
            ),
            'sql'                   => "blob NULL",
        ),
        'optionsTable' => array
        (
            'exclude'               => true,
            'inputType'             => 'dcaWizardMultilingual',
            'foreignTableCallback'  => array('Isotope\Backend\Attribute\Callback', 'initializeTableOptions'),
            'eval' => array
            (
                'fields'            => array('type', 'label', 'isDefault', 'published'),
                'tl_class'          => 'clr',
                'editButtonLabel'   => &$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsTable_edit'],
                'showOperations'    => true,
                'operations'        => array('edit', 'show'),
            ),
        ),
        'foreignKey' => array
        (
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('mandatory'=>true, 'style'=>'height:80px', 'decodeEntities'=>true),
            'sql'                   => "text NULL",
            'save_callback' => array
            (
                array('Isotope\Backend\Attribute\Callback', 'validateForeignKey'),
            ),
        ),
        'includeBlankOption' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'w50 m12'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'blankOptionLabel' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'variant_option' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'be_search' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'be_filter' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'customer_defined' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'mandatory' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'fe_filter' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'fe_search' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'fe_sorting' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'multiple' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'clr w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'size' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'default'               => 5,
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "smallint(5) unsigned NOT NULL default '0'",
        ),
        'chosen' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'extensions' => array
        (
            'exclude'               => true,
            'default'               => $GLOBALS['TL_CONFIG']['validImageTypes'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'rgxp'=>'extnd', 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'rte' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => array('Isotope\Backend\Attribute\Callback', 'getRTE'),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'multilingual' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'rgxp' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('digit', 'alpha', 'alnum', 'extnd', 'date', 'time', 'datim', 'phone', 'email', 'url', 'price', 'discount', 'surcharge'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_attribute'],
            'eval'                  => array('helpwizard'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'placeholder' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'minlength' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'"
        ),
        'maxlength' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'minval' => array
        (
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "varchar(10) NOT NULL default ''"
        ),
        'maxval' => array
        (
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "varchar(10) NOT NULL default ''"
        ),
        'step' => array
        (
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "varchar(10) NOT NULL default ''"
        ),
        'conditionField' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => array('Isotope\Backend\Attribute\Callback', 'getConditionFields'),
            'eval'                  => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(30) NOT NULL default ''",
        ),
        'fieldType' => array
        (
            'exclude'               => true,
            'inputType'             => 'radio',
            'options'               => array('checkbox', 'radio'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_attribute'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(8) NOT NULL default ''",
        ),
        'files' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'filesOnly' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50 m12'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'isGallery' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'sortBy' => array
        (
            'default'               => 'name_asc',
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('name_asc', 'name_desc', 'date_asc', 'date_desc', 'random', 'custom'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_attribute'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'path' => array
        (
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'tl_class'=>'clr'),
            'sql'                   =>  "binary(16) NULL",
        ),
        'rootNodes' => array
        (
            'exclude'               => true,
            'inputType'             => 'pageTree',
            'eval'                  => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'clr'),
            'sql'                   =>  "blob NULL",
        ),
        'checkoutRelocate' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'checkoutTargetFolder' => array
        (
            'exclude'               => true,
            'default'               => 'files/orders/##document_number##/##product_position##__{{flag::##product_name##|standardize}}/##attribute_field##',
            'inputType'             => 'text',
            'explanation'           => 'checkoutTargetFolder',
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_attribute']['checkout_tokens'],
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'clr w50', 'helpwizard'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'checkoutTargetFile' => array
        (
            'exclude'               => true,
            'default'               => '##file_target##',
            'inputType'             => 'text',
            'explanation'           => 'checkoutTargetFile',
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_attribute']['checkout_tokens'],
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50', 'helpwizard'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'datepicker' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\Backend\Attribute\Callback', 'validateDatepicker'),
            ),
        ),


        /**
         * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0
         */
        'storeFile' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'uploadFolder' => array
        (
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'tl_class'=>'clr'),
            'sql'                   => "binary(16) NULL",
        ),
        'useHomeDir' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'doNotOverwrite' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'customTpl' => array
        (
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => array('Isotope\Backend\Attribute\Callback', 'getAttributeTemplates'),
            'eval'                    => array('chosen'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
    ),
);


if (\Composer\InstalledVersions::isInstalled('terminal42/contao-fineuploader')) {
    $GLOBALS['TL_DCA']['tl_iso_attribute']['palettes']['fineUploader'] = '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},mandatory,extensions,minlength,maxlength,chunking,multiple;{store_legend:hide},checkoutRelocate';

    $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['chunking'] = array
    (
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr w50 m12'),
        'sql'                     => "char(1) NOT NULL default ''"
    );

    $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['chunkSize'] = array
    (
        'default'                 => 2000000,
        'exclude'                 => true,
        'inputType'               => 'text',
        'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50'),
        'sql'                     => "varchar(16) NOT NULL default ''"
    );
}
