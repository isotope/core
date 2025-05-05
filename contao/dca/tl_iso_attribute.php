<?php

use Composer\InstalledVersions;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Isotope\Backend\Attribute\Callback;
use Isotope\Backend\Attribute\DatabaseUpdate;
use Isotope\Backend\Attribute\OptionsWizard;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;

/*
 * Load tl_iso_product language file for field legends
 */
System::loadLanguageFile('tl_iso_product');

$GLOBALS['TL_DCA']['tl_iso_attribute'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'ctable' => [AttributeOption::getTable()],
        'onload_callback' => [
            [Callback::class, 'onLoad'],
        ],
        'onsubmit_callback' => [
            [DatabaseUpdate::class, 'onSubmit'],
        ],
        'ondelete_callback' => [
            [DatabaseUpdate::class, 'onDelete'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'type' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['legend', 'name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['name', 'field_name', 'type'],
            'format' => '<div style="float:left; width:200px">%s</div><div style="float:left; width:200px; color:#b3b3b3;">%s</div><div style="color:#b3b3b3">%s</div>',
        ],
        'operations' => [
            'edit',
            // TODO: would render editChilds operation because of ctable=tl_iso_attribute_option
            'copy',
            'delete',
            'show',
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type', 'optionsSource', 'includeBlankOption', 'variant_option', 'multiple', 'storeFile', 'files', 'rgxp', 'checkoutRelocate', 'chunking'],
        'default' => '{attribute_legend},name,field_name,type,legend',
        'text' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_search,be_filter',
        'textdigit' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minval,maxval,step,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_search,be_filter',
        'textarea' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,rte,mandatory,multilingual;{search_filters_legend},fe_search,fe_sorting,be_search',
        'select' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'selectproduct' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple;{search_filters_legend},fe_sorting',
        'selectvariant_option' => '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},optionsSource,blankOptionLabel;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'radio' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'radioproduct' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory;{search_filters_legend},fe_sorting',
        'radiovariant_option' => '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'checkbox' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'checkboxproduct' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory;{search_filters_legend},fe_sorting',
        'conditionalselect' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple,conditionField;{search_filters_legend},fe_filter,fe_sorting',
        'mediaManager' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},extensions,mandatory',
        'fileTree' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,sortBy,path,mandatory,multilingual,files,isGallery',
        'pageTree' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,mandatory,multilingual,rootNodes',
        'downloads' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,sortBy,path,mandatory,multilingual,files,isGallery',
        'upload' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},extensions,maxlength,mandatory;{store_legend:hide},checkoutRelocate',
        'media' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},path,mandatory,multilingual',
        'quantitySurcharge' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},minval,maxval,step,placeholder,mandatory',
    ],

    // Subpalettes
    'subpalettes' => [
        'storeFile' => 'uploadFolder,useHomeDir,doNotOverwrite',
        'files' => 'extensions,filesOnly,inline',
        'optionsSource_attribute' => 'options',
        'optionsSource_table' => 'optionsTable',
        'optionsSource_foreignKey' => 'foreignKey',
        'multiple' => 'size',
        'includeBlankOption' => 'blankOptionLabel',
        'checkoutRelocate' => 'checkoutTargetFolder,checkoutTargetFile',
        'chunking' => 'chunkSize',
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
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'field_name' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 30, 'unique' => true, 'doNotCopy' => true, 'doNotSaveEmpty' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(30) NOT NULL default ''",
            'save_callback' => [
                [Callback::class, 'validateFieldName'],
            ],
        ],
        'type' => [
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'inputType' => 'select',
            'options_callback' => static fn () => Attribute::getModelTypeOptions(),
            'reference' => &$GLOBALS['TL_LANG']['ATTR'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'helpwizard' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'legend' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'default' => 'options_legend',
            'inputType' => 'select',
            'options' => ['general_legend', 'meta_legend', 'pricing_legend', 'inventory_legend', 'shipping_legend', 'options_legend', 'media_legend', 'expert_legend', 'publish_legend'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_product'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'description' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'optionsSource' => [
            'filter' => true,
            'inputType' => 'radio',
            'options_callback' => static function ($dc) {
                $arrOptions = [
                    IsotopeAttributeWithOptions::SOURCE_TABLE,
                    IsotopeAttributeWithOptions::SOURCE_FOREIGNKEY,
                    IsotopeAttributeWithOptions::SOURCE_ATTRIBUTE,
                ];

                if (!$dc->activeRecord->variant_option && $dc->activeRecord->customer_defined) {
                    $arrOptions = [
                        IsotopeAttributeWithOptions::SOURCE_TABLE,
                        IsotopeAttributeWithOptions::SOURCE_PRODUCT,
                        IsotopeAttributeWithOptions::SOURCE_FOREIGNKEY,
                        IsotopeAttributeWithOptions::SOURCE_ATTRIBUTE,
                    ];
                }

                if ('checkbox' === $dc->activeRecord->type && !$dc->activeRecord->customer_defined) {
                    array_unshift($arrOptions, IsotopeAttributeWithOptions::SOURCE_NAME);
                }

                return $arrOptions;
            },
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsSource'],
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'options' => [
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'mandatory' => true,
                'dragAndDrop' => true,
                'tl_class' => 'clr',
                'columnsCallback' => [OptionsWizard::class, 'getColumns'],
            ],
            'sql' => 'blob NULL',
        ],
        'optionsTable' => [
            'inputType' => 'dcaWizardMultilingual',
            'foreignTableCallback' => [Callback::class, 'initializeTableOptions'],
            'foreignTable_callback' => [Callback::class, 'initializeTableOptions'],
            'eval' => [
                'fields' => ['type', 'label', 'isDefault', 'published'],
                'tl_class' => 'clr',
                'editButtonLabel' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsTable_edit'],
                'showOperations' => true,
                'global_operations' => ['new'],
                'operations' => ['edit', 'copy', 'cut', 'delete', 'toggle', 'show', 'new'],
            ],
        ],
        'foreignKey' => [
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'style' => 'height:80px', 'decodeEntities' => true],
            'sql' => 'text NULL',
            'save_callback' => [
                [Callback::class, 'validateForeignKey'],
            ],
        ],
        'includeBlankOption' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'blankOptionLabel' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'variant_option' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'be_search' => [
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'be_filter' => [
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'customer_defined' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'mandatory' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'fe_filter' => [
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'fe_search' => [
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'fe_sorting' => [
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'multiple' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'size' => [
            'inputType' => 'text',
            'default' => 5,
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "smallint(5) unsigned NOT NULL default '0'",
        ],
        'chosen' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'extensions' => [
            'default' => '',
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'extnd', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'rte' => [
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getRTE'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'multilingual' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'rgxp' => [
            'inputType' => 'select',
            'options' => ['digit', 'alpha', 'alnum', 'extnd', 'date', 'time', 'datim', 'phone', 'email', 'url', 'price', 'discount', 'surcharge'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_attribute'],
            'eval' => ['helpwizard' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'placeholder' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'minlength' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'maxlength' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'minval' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'maxval' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'step' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'conditionField' => [
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getConditionFields'],
            'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(30) NOT NULL default ''",
        ],
        'fieldType' => [
            'inputType' => 'radio',
            'options' => ['checkbox', 'radio'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_attribute'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'files' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'filesOnly' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'inline' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'isGallery' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'sortBy' => [
            'default' => 'name_asc',
            'inputType' => 'select',
            'options' => ['name_asc', 'name_desc', 'date_asc', 'date_desc', 'random', 'custom'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_attribute'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'path' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql' => 'binary(16) NULL',
        ],
        'rootNodes' => [
            'inputType' => 'pageTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'checkoutRelocate' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'checkoutTargetFolder' => [
            'default' => 'files/orders/##document_number##/##product_position##__{{flag::##product_name##|standardize}}/##attribute_field##',
            'inputType' => 'text',
            'explanation' => 'checkoutTargetFolder',
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['checkout_tokens'],
            'eval' => ['maxlength' => 255, 'tl_class' => 'clr w50', 'helpwizard' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'checkoutTargetFile' => [
            'default' => '##file_target##',
            'inputType' => 'text',
            'explanation' => 'checkoutTargetFile',
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['checkout_tokens'],
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'datepicker' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
            'save_callback' => [
                [Callback::class, 'validateDatepicker'],
            ],
        ],

        /*
         * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0
         */
        'storeFile' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'uploadFolder' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql' => 'binary(16) NULL',
        ],
        'useHomeDir' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'doNotOverwrite' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'customTpl' => [
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getAttributeTemplates'],
            'eval' => ['chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];
