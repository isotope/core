<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Contao\System;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;

/*
 * Load tl_iso_product language file for field legends
 */
System::loadLanguageFile('tl_iso_product');

$GLOBALS['TL_DCA']['tl_iso_attribute'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'ctable' => ['tl_iso_attribute_option'],
        'sql' =>  [
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
            'copy',
            'delete',
            'show',
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'optionsSource', 'includeBlankOption', 'variant_option', 'customer_defined', 'multiple', 'rgxp'],
        'default' => '{attribute_legend},name,field_name,type,legend',
        'text' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_filter,be_search,be_column',
        'textdigit' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minval,maxval,step,rgxp,placeholder,mandatory,multilingual;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_filter,be_search,be_column',
        'textdate' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_filter,be_search,be_column',
        'texttime' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_filter,be_search,be_column',
        'textdatim' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_filter,fe_sorting,be_filter,be_search,be_column',
        'textarea' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},minlength,maxlength,rgxp,placeholder,rte,mandatory,multilingual;{search_filters_legend},fe_search,fe_sorting,be_search',
        'select' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple;{search_filters_legend},fe_filter,fe_sorting,be_filter,be_column',
        'selectcustomer_defined' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource,includeBlankOption;{config_legend},mandatory,chosen,multiple',
        'selectvariant_option' => '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},optionsSource,blankOptionLabel;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'radio' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting,be_filter,be_column',
        'radiocustomer_defined' => '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory',
        'radiovariant_option' => '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},optionsSource;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'checkbox' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting,be_filter',
        'checkboxcustomer_defined' => '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},optionsSource;{config_legend},mandatory',
        'mediaManager' => '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},extensions,mandatory',
    ],
    'subpalettes' => [
        'optionsSource_attribute' => 'options',
        'optionsSource_table' => 'optionsTable',
        'optionsSource_foreignKey' => 'foreignKey',
        'multiple' => 'size',
        'includeBlankOption' => 'blankOptionLabel',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'name' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'field_name' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 30, 'unique' => true, 'doNotCopy' => true, 'doNotSaveEmpty' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 30, 'default' => ''],
        ],
        'type' => [
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['ATTR'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'helpwizard' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
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
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'description' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'optionsSource' => [
            'filter' => true,
            'inputType' => 'radio',
            'enum' => OptionsSource::class,
            'options_callback' => static function (DataContainer $dc) {
                $arrOptions = [
                    OptionsSource::Table->value,
                    OptionsSource::ForeignKey->value,
                    OptionsSource::Attribute->value,
                ];

                $currentRecord = $dc->getCurrentRecord();

                if (!($currentRecord['variant_option'] ?? null) && ($currentRecord['customer_defined'] ?? null)) {
                    $arrOptions = [
                        OptionsSource::Table->value,
                        OptionsSource::Product->value,
                        OptionsSource::ForeignKey->value,
                        OptionsSource::Attribute->value,
                    ];
                }

                if ('checkbox' === ($currentRecord['type'] ?? null) && !($currentRecord['customer_defined'] ?? null)) {
                    array_unshift($arrOptions, OptionsSource::Name->value);
                }

                return $arrOptions;
            },
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => ['type' => Types::STRING, 'length' => 16, 'default' => ''],
        ],
        'options' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'value' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['value'],
                    'inputType' => 'text',
                    'eval' => ['mandatory' => true, 'maxlength' => 64],
                ],
                'label' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['label'],
                    'inputType' => 'text',
                    'eval' => ['mandatory' => true],
                ],
                'default' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['default'],
                    'inputType' => 'checkbox',
                ],
                'group' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['group'],
                    'inputType' => 'checkbox',
                ],
            ],
            'eval' => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
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
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'includeBlankOption' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'blankOptionLabel' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'variant_option' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'be_filter' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w25'],
            'sql' =>  ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'be_search' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w25'],
            'sql' =>  ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'be_column' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w25'],
            'sql' =>  ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'customer_defined' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'mandatory' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'fe_filter' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w25'],
            'sql' =>  ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'fe_search' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w25'],
            'sql' =>  ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'fe_sorting' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w25'],
            'sql' =>  ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'multiple' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'size' => [
            'inputType' => 'text',
            'default' => 5,
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::SMALLINT, 'unsigned' => true, 'default' => 0],
        ],
        'chosen' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'extensions' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'extnd', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'rte' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'multilingual' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'rgxp' => [
            'inputType' => 'select',
            'options' => ['digit', 'alpha', 'alnum', 'extnd', 'date', 'time', 'datim', 'phone', 'email', 'url', 'price', 'discount', 'surcharge'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_attribute'],
            'eval' => ['helpwizard' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'placeholder' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'minlength' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'maxlength' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'minval' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 10, 'default' => ''],
        ],
        'maxval' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 10, 'default' => ''],
        ],
        'step' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 10, 'default' => ''],
        ],
        'datepicker' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'customTpl' => [
            'inputType' => 'select',
            'eval' => ['chosen' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
    ],
];
