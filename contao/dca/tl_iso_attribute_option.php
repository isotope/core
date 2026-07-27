<?php

use Contao\DataContainer;
use Contao\Input;
use Terminal42\DcMultilingualBundle\Driver;

$GLOBALS['TL_DCA']['tl_iso_attribute_option'] = [
    'config' => [
        'dataContainer' => Driver::class,
        'enableVersioning' => true,
        'dynamicPtable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'ptable,pid' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter,search,limit',
            'headerFields' => ['name', 'field_name', 'type', 'variant_option', 'customer_defined'],
        ],
        'label' => [
            'fields' => ['label'],
            'label' => '%s',
        ],
    ],
    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{label_legend},type',
        'option' => '{label_legend},type,isDefault,label;{price_legend:hide},price;{expert_legend:hide},cssClass;{publish_legend},published',
        'group' => '{label_legend},type,label;{expert_legend:hide},cssClass;{publish_legend},published',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'ptable' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'langPid' => [
            'eval' => ['doNotShow' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'language' => [
            'eval' => ['doNotShow' => true],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'field_name' => [
            'eval' => ['doNotShow' => true],
            'sql' => "varchar(30) NOT NULL default ''",
        ],
        'type' => [
            'default' => 'option',
            'inputType' => 'radio',
            'options' => ['option', 'group'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['type'],
            'eval' => ['tl_class' => 'w50', 'doNotCopy' => true, 'submitOnChange' => true],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'isDefault' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12', 'doNotCopy' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'label' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'translatableFor' => '*', 'tl_class' => 'clr long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'price' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_attribute_option'][Input::get('popup') ? 'price' : 'price_short'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'rgxp' => 'discount', 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
            'save_callback' => [
                static function ($varValue) {
                    if ('' !== $varValue && !str_contains((string) $varValue, '.') && !str_contains((string) $varValue, '%')) {
                        $varValue = number_format($varValue, 2, '.', '');

                        if ($varValue > 0) {
                            $varValue = '+'.$varValue;
                        }
                    }

                    return $varValue;
                },
            ],
        ],
        'cssClass' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'published' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'doNotCopy' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
    ],
];
