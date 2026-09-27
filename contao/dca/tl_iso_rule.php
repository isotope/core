<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_rule'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_iso_rule_condition'],
        'enableVersioning' => false,
        'markAsCopy' => 'name',
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['name'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['name'],
        ],
    ],
    'palettes' => [
        'default' => '{rule_legend},name;{conditions_legend},conditions;{application_legend},enabled,priority',
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
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'conditions' => [
            'inputType' => 'dcaWizard',
            'foreignTable' => 'tl_iso_rule_condition',
            'eval' => [
                'tl_class' => 'clr',
                'editButtonLabel' => &$GLOBALS['TL_LANG']['tl_iso_rule']['conditions']['edit'],
                'showOperations' => true,
                'global_operations' => ['new'],
                'operations' => ['edit', 'copy', 'cut', 'delete', 'toggle', 'show', 'new'],
            ],
        ],
        'enabled' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'filter' => true,
            'eval' => ['doNotCopy' => true, 'tl_class' => 'w33 m12'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'priority' => [
            'sorting' => true,
            'flag' => DataContainer::SORT_BOTH,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'tl_class' => 'w33'],
            'sql' => ['type' => Types::INTEGER, 'default' => 0],
        ],
    ],
];
