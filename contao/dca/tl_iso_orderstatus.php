<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Store\Order\StatusType;
use Isotope\CoreBundle\Store\Order\TransactionState;

$GLOBALS['TL_DCA']['tl_iso_orderstatus'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'transaction_state' => 'index',
                'type,alias' => 'unique',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['type', 'sorting'],
            'panelLayout' => 'search,filter,limit',
            'defaultSearchField' => 'name',
        ],
        'label' => [
            'fields' => ['type', 'name', 'alias', 'sorting'],
            'showColumns' => true,
        ],
    ],
    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{name_legend},type,alias,sorting,name;{workflow_legend},workflow',
        'order' => '{name_legend},type,alias,sorting,color,name,label,welcomescreen;{workflow_legend},workflow',
        'transaction' => '{name_legend},type,alias,sorting,color,name,label;{workflow_legend},transaction_state,workflow',
        'delivery' => '{name_legend},type,alias,sorting,color,name,label;{workflow_legend},workflow',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'inputType' => 'select',
            'filter' => true,
            'enum' => StatusType::class,
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w25'],
            'sql' => "varchar(16) NOT NULL default 'order'",
        ],
        'alias' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'rgxp' => 'alias', 'maxlength' => 32, 'tl_class' => 'w25'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'sorting' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w25'],
            'sql' => ['type' => Types::INTEGER, 'default' => 0, 'unsigned' => true],
        ],
        'color' => [
            'inputType' => 'text',
            'eval' => ['minlength' => 6, 'maxlength' => 6, 'colorpicker' => true, 'isHexColor' => true, 'decodeEntities' => true, 'tl_class' => 'w25'],
            'sql' => "varchar(6) NOT NULL default ''",
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'label' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'transaction_state' => [
            'inputType' => 'select',
            'filter' => true,
            'enum' => TransactionState::class,
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['transaction_state_options'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'workflow' => [
            'inputType' => 'checkboxWizard',
            'filter' => true,
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['multiple' => true, 'csv' => ',', 'tl_class' => 'clr'],
            'relation' => ['table' => 'tl_iso_orderstatus', 'field' => 'alias', 'type' => 'hasOne', 'load' => 'lazy'],
            'sql' => 'text NULL',
        ],
        'welcomescreen' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
    ],
];
