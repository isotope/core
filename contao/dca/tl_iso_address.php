<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_address'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'notSortable' => true,
        'dynamicPtable' => true,
        'sql' =>  [
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
            'defaultSearchField' => 'email',
            'fields' => ['store_id'],
            'flag' => DataContainer::SORT_ASC,
            'panelLayout' => 'search,filter,limit',
        ],
        'label' => [
            'fields' => ['country'],
        ],
    ],
    'palettes' => [
        'default' => '{personal_legend},gender,salutation,firstname,lastname,dateOfBirth;{company_legend},company,vat_no;{address_legend},street_1,street_2,street_3,postal,city,subdivision,country;{contact_legend},email,phone;{store_legend:hide},store_id',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'ptable' => [
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'store_id' => [
            'filter' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 2, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'gender' => [
            'inputType' => 'select',
            'options' => ['male', 'female', 'other'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['includeBlankOption' => true, 'feEditable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50', 'autocomplete' => 'sex'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'default' => ''],
        ],
        'salutation' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'firstname' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50', 'autocomplete' => 'given-name'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'lastname' => [
            'search' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50', 'autocomplete' => 'family-name'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'dateOfBirth' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50', 'autocomplete' => 'bday'],
            'sql' => ['type' => Types::STRING, 'length' => 11, 'default' => ''],
        ],
        'company' => [
            'search' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => false, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50', 'autocomplete' => 'organization'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'vat_no' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'street_1' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50', 'autocomplete' => 'address-line1'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'street_2' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50', 'autocomplete' => 'address-line2'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'street_3' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50', 'autocomplete' => 'address-line3'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'postal' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'clr w50', 'autocomplete' => 'postal-code'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'default' => ''],
        ],
        'city' => [
            'filter' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50', 'autocomplete' => 'address-level2'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'subdivision' => [
            'inputType' => 'conditionalselect',
            'eval' => ['feEditable' => true, 'feGroup' => 'address', 'conditionField' => 'country', 'includeBlankOption' => true, 'tl_class' => 'w50', 'autocomplete' => 'address-level1'],
            'sql' => ['type' => Types::STRING, 'length' => 10, 'default' => ''],
        ],
        'country' => [
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'feEditable' => true, 'feGroup' => 'address', 'tl_class' => 'w50', 'chosen' => true, 'autocomplete' => 'country'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'default' => ''],
        ],
        'phone' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => false, 'maxlength' => 64, 'rgxp' => 'phone', 'feEditable' => true, 'feGroup' => 'contact', 'tl_class' => 'w50', 'autocomplete' => 'tel'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'email' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'rgxp' => 'email', 'feEditable' => true, 'feGroup' => 'contact', 'tl_class' => 'w50', 'autocomplete' => 'email'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
    ],
];
