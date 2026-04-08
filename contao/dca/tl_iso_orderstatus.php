<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Store\Order\StatusType;

$GLOBALS['TL_DCA']['tl_iso_orderstatus'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'paid' => 'index',
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
        'order' => '{name_legend},type,alias,sorting,name,label,color,welcomescreen;{workflow_legend},workflow',
        'transaction' => '{name_legend},type,alias,sorting,name,label,color,paid;{workflow_legend},workflow',
        'delivery' => '{name_legend},type,alias,sorting,name,label,color;{workflow_legend},workflow',
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
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
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
        'color' => [
            'inputType' => 'text',
            'eval' => ['minlength' => 6, 'maxlength' => 6, 'colorpicker' => true, 'isHexColor' => true, 'decodeEntities' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(6) NOT NULL default ''",
        ],
        'workflow' => [
            'inputType' => 'checkboxWizard',
            'filter' => true,
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['multiple' => true, 'csv' => ',', 'tl_class' => 'clr'],
            'relation' => ['table' => 'tl_iso_orderstatus', 'field' => 'alias', 'type' => 'hasOne', 'load' => 'lazy'],
            'sql' => 'text NULL',
        ],
        // TODO: this should no longer be necessary
        'paid' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'welcomescreen' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
