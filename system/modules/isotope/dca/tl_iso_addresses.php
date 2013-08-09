<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Table tl_iso_addresses
 */
$GLOBALS['TL_DCA']['tl_iso_addresses'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => false,
        'ptable'                    => '',
        'dynamicPtable'             => true,
        'onload_callback'           => array(),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid;pid,store_id' => 'index',
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 4,
            'headerFields'          => array('firstname','lastname', 'username'),
            'disableGrouping'       => true,
            'flag'                  => 1,
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => array('Isotope\tl_iso_addresses','renderLabel')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_addresses']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_addresses']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif'
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_addresses']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_addresses']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                   => '{store_legend},label,store_id;{personal_legend},salutation,firstname,lastname,company,vat_no;{address_legend},street_1,street_2,street_3,postal,city,subdivision,country;{contact_legend},email,phone;{default_legend:hide},isDefaultBilling,isDefaultShipping',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'ptable' => array
        (
            'sql'                 =>  "varchar(64) NOT NULL default ''",
        ),
        'label' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['label'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'store_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['store_id'],
            'exclude'               => true,
            'filter'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>2, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(2) unsigned NOT NULL default '0'",
        ),
        'salutation' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['salutation'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'firstname' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['firstname'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default''",
        ),
        'lastname' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['lastname'],
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'flag'                  => 1,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'company' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['company'],
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'flag'                  => 1,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'vat_no' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['vat_no'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_1' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['street_1'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_2' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['street_2'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_3' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['street_3'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'postal' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['postal'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'clr w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'city' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['city'],
            'exclude'               => true,
            'filter'                => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'subdivision' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['subdivision'],
            'exclude'               => true,
            'sorting'               => true,
            'inputType'             => 'conditionalselect',
            'options_callback'      => array('Isotope\Backend', 'getSubdivisions'),
            'eval'                  => array('feEditable'=>true, 'feGroup'=>'address', 'conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'country' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['country'],
            'exclude'               => true,
            'filter'                => true,
            'sorting'               => true,
            'inputType'             => 'select',
            'options'               => array_keys($this->getCountries()),
            'reference'             => $this->getCountries(),
            'eval'                  => array('mandatory'=>true, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'phone' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['phone'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>64, 'rgxp'=>'phone', 'feEditable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'email' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['email'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>64, 'rgxp'=>'email', 'feEditable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'isDefaultBilling' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultBilling'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('feEditable'=>true, 'feGroup'=>'login', 'membersOnly'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\tl_iso_addresses', 'updateDefault'),
            ),
        ),
        'isDefaultShipping' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultShipping'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('feEditable'=>true, 'feGroup'=>'login', 'membersOnly'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
            'save_callback' => array
            (
                array('Isotope\tl_iso_addresses', 'updateDefault'),
            ),
        ),
    )
);

/**
 * Dynamically add parent table
 */
if (\Input::get('do') == 'member') {
	$GLOBALS['TL_DCA']['tl_iso_addresses']['config']['ptable'] = 'tl_member';
} elseif (\Input::get('do') == 'iso_orders') {
	$GLOBALS['TL_DCA']['tl_iso_addresses']['config']['ptable'] = 'tl_iso_product_collection';
}
