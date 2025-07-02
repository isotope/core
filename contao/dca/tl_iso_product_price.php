<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\MemberGroupModel;
use Isotope\Model\Config;
use Isotope\Model\TaxClass;

$GLOBALS['TL_DCA']['tl_iso_product_price'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ptable' => 'tl_iso_product',
        'ctable' => ['tl_iso_product_pricetier'],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'config_id,member_group,start,stop,pid' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['id'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['id', 'name', 'alias', 'sku'],
            'disableGrouping' => true,
        ],
        'operations' => [
            'edit',
            'copy',
            'cut',
            'delete',
            'show',
        ],
    ],
    'palettes' => [
        'default' => '{price_legend},price_tiers,tax_class;{limit_legend},config_id,member_group,start,stop',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'price_tiers' => [
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'doNotSaveEmpty' => true,
                'tl_class' => 'clr',
                'disableSorting' => true,
                'columnFields' => [
                    'min' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_iso_product_price']['price_tier_columns']['min'],
                        'inputType' => 'text',
                        'eval' => ['mandatory' => true, 'rgxp' => 'digit'],
                    ],
                    'price' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_iso_product_price']['price_tier_columns']['price'],
                        'inputType' => 'text',
                        'eval' => ['mandatory' => true, 'rgxp' => 'digit'],
                    ],
                ],
            ],
        ],
        'tax_class' => [
            'default' => static fn () => TaxClass::findFallback()?->id,
            'inputType' => 'select',
            'foreignKey' => TaxClass::getTable().'.name',
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class' => 'clr'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'config_id' => [
            'inputType' => 'select',
            'foreignKey' => Config::getTable().'.name',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'member_group' => [
            'inputType' => 'select',
            'foreignKey' => MemberGroupModel::getTable().'.name',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'start' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
