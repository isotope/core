<?php

use Composer\InstalledVersions;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
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
        '__selector__' => ['type', 'optionsSource', 'includeBlankOption', 'variant_option', 'multiple', 'rgxp'],
        'default' => '{attribute_legend},name,field_name,type,legend',
        'text' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_search,be_filter',
        'textdigit' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minval,maxval,step,rgxp,placeholder,mandatory,multilingual;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_search,be_filter',
        'textdate' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_search,be_filter',
        'texttime' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_search,be_filter',
        'textdatim' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_search,be_filter',
        'textarea' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,rte,mandatory,multilingual;{search_filters_legend},fe_search,fe_sorting,be_search',
        'select' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'selectproduct' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple;{search_filters_legend},fe_sorting',
        'selectvariant_option' => '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},optionsSource,blankOptionLabel;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'radio' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'radioproduct' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory;{search_filters_legend},fe_sorting',
        'radiovariant_option' => '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'checkbox' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'checkboxproduct' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory;{search_filters_legend},fe_sorting',
        'mediaManager' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},extensions,mandatory',
    ],

    // Subpalettes
    'subpalettes' => [
        'optionsSource_attribute' => 'options',
        'optionsSource_table' => 'optionsTable',
        'optionsSource_foreignKey' => 'foreignKey',
        'multiple' => 'size',
        'includeBlankOption' => 'blankOptionLabel',
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
        'datepicker' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'customTpl' => [
            'inputType' => 'select',
            'eval' => ['chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];
