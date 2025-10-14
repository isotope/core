<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Isotope\Backend;
use Isotope\Backend\Address\Callback;
use Isotope\CompatibilityHelper;

$GLOBALS['TL_DCA']['tl_iso_address'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'notSortable' => true,
        'ptable' => '',
        'dynamicPtable' => true,
        'onload_callback' => [],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,store_id' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'headerFields' => ['firstname', 'lastname', 'username'],
            'disableGrouping' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => [Callback::class, 'renderLabel'],
        ],
    ],
    'palettes' => [
        'default' => '{store_legend},label,store_id;{personal_legend},gender,salutation,firstname,lastname,dateOfBirth,company,vat_no;{address_legend},street_1,street_2,street_3,street_number_1,street_number_2,street_number_3,postal,city,subdivision,country;{contact_legend},email,phone;{default_legend:hide},isDefaultBilling,isDefaultShipping',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'ptable' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'label' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'store_id' => [
            'filter' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 2, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(2) unsigned NOT NULL default '0'",
        ],
        'gender' => [
            'inputType' => 'select',
            'options' => ['male', 'female', 'other'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['includeBlankOption' => true, 'feEditable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'salutation' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'firstname' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'lastname' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'dateOfBirth' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(11) NOT NULL default ''",
        ],
        'company' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => false, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'vat_no' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_1' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_2' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_3' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'postal' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'clr w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'city' => [
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'subdivision' => [
            'sorting' => true,
            'inputType' => 'conditionalselect',
            'options_callback' => [Backend::class, 'getSubdivisions'],
            'eval' => ['feEditable' => true, 'feGroup' => 'address', 'conditionField' => 'country', 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'country' => [
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'phone' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => false, 'maxlength' => 64, 'rgxp' => 'phone', 'feEditable' => true, 'feGroup' => 'contact', 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'email' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'rgxp' => 'email', 'feEditable' => true, 'feGroup' => 'contact', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'isDefaultBilling' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['feEditable' => true, 'feGroup' => 'login', 'membersOnly' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
            'save_callback' => [
                [Callback::class, 'updateDefault'],
            ],
        ],
        'isDefaultShipping' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['feEditable' => true, 'feGroup' => 'login', 'membersOnly' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
            'save_callback' => [
                [Callback::class, 'updateDefault'],
            ],
        ],
    ],
];

/*
 * Dynamically add parent table
 */
if ('member' === Input::get('do')) {
    $GLOBALS['TL_DCA']['tl_iso_address']['config']['ptable'] = 'tl_member';
} elseif ('iso_orders' === Input::get('do')) {
    $GLOBALS['TL_DCA']['tl_iso_address']['config']['ptable'] = 'tl_iso_product_collection';
}
