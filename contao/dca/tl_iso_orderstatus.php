<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Store\Order\StatusType;
use Isotope\CoreBundle\Store\Order\TransactionState;

$GLOBALS['TL_DCA']['tl_iso_orderstatus'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
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
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'type' => [
            'inputType' => 'select',
            'filter' => true,
            'enum' => StatusType::class,
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w25'],
            'sql' => ['type' => Types::STRING, 'length' => 16, 'default' => 'order'],
        ],
        'alias' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'rgxp' => 'alias', 'maxlength' => 32, 'tl_class' => 'w25'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'default' => ''],
        ],
        'sorting' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w25'],
            'sql' =>  ['type' => Types::INTEGER, 'default' => 0, 'unsigned' => true],
        ],
        'color' => [
            'inputType' => 'text',
            'eval' => ['minlength' => 6, 'maxlength' => 6, 'colorpicker' => true, 'isHexColor' => true, 'decodeEntities' => true, 'tl_class' => 'w25'],
            'sql' => ['type' => Types::STRING, 'length' => 6, 'default' => ''],
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'label' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'transaction_state' => [
            'inputType' => 'select',
            'filter' => true,
            'enum' => TransactionState::class,
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['transaction_state_options'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => ['type' => Types::STRING, 'length' => 16, 'default' => ''],
        ],
        'workflow' => [
            'inputType' => 'checkboxWizard',
            'filter' => true,
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['multiple' => true, 'csv' => ',', 'tl_class' => 'clr'],
            'relation' => ['table' => 'tl_iso_orderstatus', 'field' => 'alias', 'type' => 'hasOne', 'load' => 'lazy'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'welcomescreen' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
    ],
];
