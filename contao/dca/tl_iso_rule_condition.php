<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_rule_condition'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_iso_rule',
        'enableVersioning' => false,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,sorting' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['name', 'enabled'],
        ],
        'label' => [
            'fields' => ['type'],
            'format' => '%s',
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'operator'],
        'default' => '{condition_legend},type',
        'cart_total' => '{condition_legend},type,operator,amount',
        'cart_totalrange' => '{condition_legend},type,operator,amount_from,amount_to',
        'member_group' => '{condition_legend},type,operator,member_groups',
        'member_authenticated' => '{condition_legend},type,operator',
        'store_config' => '{condition_legend},type,operator,config_ids',
        'store_currency' => '{condition_legend},type,operator,currencies',
        'store_date' => '{condition_legend},type,start,stop',
        'store_time' => '{condition_legend},type,start_time,stop_time',
    ],
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'sorting' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'type' => [
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_rule_condition'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'operator' => [
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'amount' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'price', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'amount_from' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'price', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'amount_to' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'price', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'member_groups' => [
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true, 'csv' => ','],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'config_ids' => [
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_iso_config.name',
            'eval' => ['mandatory' => true, 'multiple' => true, 'csv' => ','],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'currencies' => [
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'multiple' => true, 'csv' => ',', 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'start' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'clr w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'stop' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'start_time' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'time', 'tl_class' => 'clr w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
        'stop_time' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'time', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
        ],
    ],
];
