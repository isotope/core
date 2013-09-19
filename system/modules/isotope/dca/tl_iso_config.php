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
 * Table tl_iso_config
 */
$GLOBALS['TL_DCA']['tl_iso_config'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'closed'                    => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\tl_iso_config', 'checkPermission'),
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\Backend', 'truncateProductCache'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 1,
            'fields'                => array('name'),
            'flag'                  => 1,
        ),
        'label' => array
        (
            'fields'                => array('name', 'fallback'),
            'format'                => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            'label_callback'        => array('Isotope\tl_iso_config', 'addIcon')
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'              => 'mod=&table=',
                'class'             => 'header_back',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_config']['new'],
                'href'              => 'act=create',
                'class'             => 'header_new',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_config']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif',
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_config']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\tl_iso_config', 'copyConfig'),
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_config']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\tl_iso_config', 'deleteConfig'),
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_config']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif',
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('currencySymbol', 'currencyAutomator'),
        'default'                   => '
            {name_legend},name,label,fallback;
            {address_legend:hide},firstname,lastname,company,vat_no,street_1,street_2,street_3,postal,city,country,subdivision,email,phone;
            {checkout_legend},billing_countries,shipping_countries,billing_fields,shipping_fields,billing_country,shipping_country,limitMemberCountries;
            {currency_legend},priceRoundPrecision,priceRoundIncrement,currency,currencyFormat,currencyPosition,currencySymbol;
            {converter_legend:hide},priceCalculateFactor,priceCalculateMode,currencyAutomator;
            {order_legend:hide},orderPrefix,orderDigits,orderstatus_new,orderstatus_error;
            {config_legend},templateGroup,cartMinSubtotal;
            {url_legend},urlMatrix;
            {products_legend},newProductPeriod',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'currencySymbol'            => 'currencySpace',
        'currencyAutomator'         => 'currencyOrigin,currencyProvider',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['name'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'label' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['label'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'fallback' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['fallback'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('doNotCopy'=>true, 'fallback'=>true, 'tl_class'=>'w50 m12'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'firstname' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['firstname'],
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'flag'                  => 1,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'lastname' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['lastname'],
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'flag'                  => 1,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'company' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['company'],
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'flag'                  => 1,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'vat_no' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['vat_no'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_1' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['street_1'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_2' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['street_2'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_3' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['street_3'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'postal' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['postal'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>32, 'tl_class'=>'clr w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'city' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['city'],
            'exclude'               => true,
            'filter'                => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'subdivision' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['subdivision'],
            'exclude'               => true,
            'sorting'               => true,
            'inputType'             => 'conditionalselect',
            'options_callback'      => array('Isotope\Backend', 'getSubdivisions'),
            'eval'                  => array('conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'country' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['country'],
            'exclude'               => true,
            'filter'                => true,
            'sorting'               => true,
            'inputType'             => 'select',
            'default'               => (string) BackendUser::getInstance()->country,
            'options'               => \System::getCountries(),
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(2) NOT NULL default ''",
        ),
        'phone' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['phone'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>64, 'rgxp'=>'phone', 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'email' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['email'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>64, 'rgxp'=>'email', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'shipping_countries' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => \System::getCountries(),
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
        ),
        'shipping_fields' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields'],
            'exclude'               => true,
            'inputType'             => 'fieldWizard',
            'eval'                  => array('multiple'=>true, 'tl_class'=>'w50 w50h', 'table'=>'tl_iso_addresses', 'helpwizard'=>true),
            'explanation'           => 'isoFieldWizard',
            'sql'                   => "blob NULL",
        ),
        'shipping_country' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_country'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => \System::getCountries(),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(2) NOT NULL default ''",
        ),
        'billing_countries' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => \System::getCountries(),
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL"
        ),
        'billing_fields' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields'],
            'exclude'               => true,
            'inputType'             => 'fieldWizard',
            'eval'                  => array('mandatory'=>true, 'multiple'=>true, 'table'=>'tl_iso_addresses', 'tl_class'=>'clr w50 w50h', 'helpwizard'=>true),
            'explanation'           => 'isoFieldWizard',
            'sql'                   => "blob NULL",
        ),
        'billing_country' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_country'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => \System::getCountries(),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(2) NOT NULL default ''",
        ),
        'limitMemberCountries' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'priceRoundPrecision' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision'],
            'exclude'               => true,
            'default'               => '2',
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>1, 'rgpx'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(1) unsigned NOT NULL default '2'",
        ),
        'priceRoundIncrement' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('0.01', '0.05'),
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(4) NOT NULL default ''",
        ),
        'cartMinSubtotal' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal'],
            'exclude'               => true,
            'default'               => '',
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>13, 'rgpx'=>'price', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'currency' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['currency'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => &$GLOBALS['TL_LANG']['CUR'],
            'eval'                  => array('includeBlankOption'=>true, 'mandatory'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(3) NOT NULL default ''",
        ),
        'currencySymbol' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'currencySpace' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'currencyPosition' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition'],
            'exclude'               => true,
            'inputType'             => 'radio',
            'default'               => 'left',
            'options'               => array('left', 'right'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(5) NOT NULL default ''",
        ),
        'currencyFormat' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array_keys($GLOBALS['ISO_NUM']),
            'eval'                  => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(20) NOT NULL default ''",
        ),
        'priceCalculateFactor' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor'],
            'exclude'               => true,
            'default'               => 1,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'priceCalculateMode' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode'],
            'exclude'               => true,
            'default'               => 'mul',
            'inputType'             => 'radio',
            'options'               => array('mul', 'div'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(3) NOT NULL default ''",
        ),
        'currencyAutomator' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyAutomator'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'clr', 'helpwizard'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'currencyOrigin' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyOrigin'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => &$GLOBALS['TL_LANG']['CUR'],
            'eval'                  => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(3) NOT NULL default ''",
        ),
        'currencyProvider' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyProvider'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('ecb.int', 'admin.ch'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'orderPrefix' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'orderDigits' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits'],
            'exclude'               => true,
            'default'               => 4,
            'inputType'             => 'select',
            'options'               => range(1, 9),
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "int(1) unsigned NOT NULL default '4'",
        ),
        'orderstatus_new' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_new'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => 'tl_iso_orderstatus.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'orderstatus_error' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_error'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => 'tl_iso_orderstatus.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'templateGroup' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => array('Isotope\tl_iso_config', 'getTemplateFolders'),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'urlMatrix' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['urlMatrix'],
            'exclude'               => true,
            'inputType'             => 'multiColumnWizard',
            'eval'                  => array
            (
                'tl_class'          =>'clr',
                'columnFields'      => array
                (
                    'original'  => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['urlMatrix']['original'],
                        'inputType' => 'text',
                        'eval'      => array('disabled'=>true)
                    ),
                    'custom'    => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['urlMatrix']['custom'],
                        'inputType' => 'text',
                    )
                ),
                'hideButtons'       => true,
                'urlParams'         => array('product', 'step')
            ),
            'sql'                   => "blob NULL",
            'load_callback'         => array
            (
                array('Isotope\tl_iso_config', 'loadUrlMatrix'),
            ),
            'save_callback'         => array
            (
                array('Isotope\tl_iso_config', 'saveUrlMatrix'),
            ),
        ),
        'newProductPeriod' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_iso_config']['newProductPeriod'],
            'exclude'                   => true,
            'default'                   => array('unit'=>'days'),
            'inputType'                 => 'timePeriod',
            'options'                   => array('minutes', 'hours', 'days', 'weeks', 'months', 'years'),
            'reference'                 => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
            'eval'                      => array('rgxp'=>'digit', 'maxlength'=>5, 'tl_class'=>'w50'),
            'sql'                       => "varchar(255) NOT NULL default ''"
        ),
    )
);
