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
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'closed'					  => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\tl_iso_config', 'checkPermission'),
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('name'),
            'flag'					  => 1,
        ),
        'label' => array
        (
            'fields'                  => array('name', 'fallback'),
            'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            'label_callback'		  => array('Isotope\tl_iso_config', 'addIcon')
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'                => 'mod=&table=',
                'class'               => 'header_back',
                'attributes'          => 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['new'],
                'href'                => 'act=create',
                'class'               => 'header_new',
                'attributes'          => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset();"',
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif',
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif',
                'button_callback'     => array('Isotope\tl_iso_config', 'copyConfig'),
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'     => array('Isotope\tl_iso_config', 'deleteConfig'),
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif',
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'				  => array('currencySymbol', 'currencyAutomator'),
        'default'                     => '
            {name_legend},name,label,fallback,store_id;
            {address_legend:hide},firstname,lastname,company,vat_no,street_1,street_2,street_3,postal,city,country,subdivision,email,phone;
            {config_legend},orderPrefix,orderDigits,templateGroup;
            {checkout_legend},billing_countries,shipping_countries,billing_fields,shipping_fields,billing_country,shipping_country,limitMemberCountries;
            {price_legend},priceRoundPrecision,priceRoundIncrement,cartMinSubtotal;
            {currency_legend},currency,currencyFormat,currencyPosition,currencySymbol;
            {converter_legend:hide},priceCalculateFactor,priceCalculateMode,currencyAutomator;
            {order_legend:hide},orderstatus_new,orderstatus_error,invoiceLogo;
            {images_legend},gallery,missing_image_placeholder,imageSizes',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'currencySymbol'				=> 'currencySpace',
        'currencyAutomator'				=> 'currencyOrigin,currencyProvider',
    ),

    // Fields
    'fields' => array
    (
        'name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['name'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'label' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['label'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'fallback' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['fallback'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'						=> array('doNotCopy'=>true, 'fallback'=>true, 'tl_class'=>'w50 m12'),
        ),
        'store_id' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['store_id'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'maxlength'=>2, 'tl_class'=>'w50'),
        ),
        'firstname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['firstname'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'lastname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['lastname'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'company' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['company'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'vat_no' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['vat_no'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'street_1' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['street_1'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'street_2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['street_2'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'street_3' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['street_3'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'postal' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['postal'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>32, 'tl_class'=>'clr w50'),
        ),
        'city' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['city'],
            'exclude'                 => true,
            'filter'                  => true,
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'subdivision' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['subdivision'],
            'exclude'                 => true,
            'sorting'                 => true,
            'inputType'               => 'conditionalselect',
            'options_callback'		  => array('Isotope\Backend', 'getSubdivisions'),
            'eval'                    => array('conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
        ),
        'country' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['country'],
            'exclude'                 => true,
            'filter'                  => true,
            'sorting'                 => true,
            'inputType'               => 'select',
            'default'				  => $this->User->country,
            'options'                 => $this->getCountries(),
            'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
        ),
        'phone' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['phone'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'tl_class'=>'w50'),
        ),
        'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['email'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'email', 'tl_class'=>'w50')
        ),
        'shipping_countries' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => $this->getCountries(),
            'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true)
        ),
        'shipping_fields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields'],
            'exclude'                 => true,
            'inputType'               => 'fieldWizard',
            'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'w50 w50h', 'table'=>'tl_iso_addresses')
        ),
        'shipping_country' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_country'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => $this->getCountries(),
            'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
        ),
        'billing_countries' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => $this->getCountries(),
            'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true)
        ),
        'billing_fields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields'],
            'exclude'                 => true,
            'inputType'               => 'fieldWizard',
            'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'table'=>'tl_iso_addresses', 'tl_class'=>'clr w50 w50h'),
        ),
        'billing_country' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_country'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => $this->getCountries(),
            'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
        ),
        'orderPrefix' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
        ),
        'orderDigits' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits'],
            'exclude'                 => true,
            'default'				  => 4,
            'inputType'               => 'select',
            'options'				  => range(1, 9),
            'eval'                    => array('tl_class'=>'w50'),
        ),
        'templateGroup' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('fieldType'=>'radio', 'path'=>'templates', 'tl_class'=>'clr')
        ),
        'limitMemberCountries' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'					  => array('tl_class'=>'w50'),
        ),
        'orderstatus_new' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_new'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options'                 => \Isotope\Backend::getOrderStatus(),
            'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
        ),
        'orderstatus_error' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_error'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options'                 => \Isotope\Backend::getOrderStatus(),
            'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
        ),
        'invoiceLogo' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'jpg,jpeg,gif,png,tif,tiff', 'tl_class'=>'clr'),
        ),
        'gallery' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['gallery'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'default',
            'options'                 => array_keys(\Isotope\Factory\Gallery::getClasses()),
            'reference'               => \Isotope\Factory\Gallery::getLabels(),
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'clr', 'helpwizard'=>true),
        ),
        'missing_image_placeholder' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions' => 'jpg,jpeg,gif,png,tif,tiff', 'tl_class'=>'clr'),
        ),
        'imageSizes' => array
        (
            'label'					  => &$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes'],
            'exclude'                 => true,
            'inputType'				  => 'multiColumnWizard',
            'default'                 => array
            (
                array('name'=>'gallery'),
                array('name'=>'thumbnail'),
                array('name'=>'medium'),
                array('name'=>'large'),
            ),
            'eval'                    => array
            (
                'mandatory'           => true,
                'tl_class'            => 'clr',
                'disableSorting'      => true,
                'columnFields' => array
                (
                    'name' => array
                    (
                        'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwName'],
                        'inputType'   => 'text',
                        'eval'        => array('mandatory'=>true, 'rgxp'=>'alpha', 'spaceToUnderscore'=>true, 'class'=>'tl_text_4'),
                    ),
                    'width' => array
                    (
                        'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwWidth'],
                        'inputType'   => 'text',
                        'eval'        => array('rgxp'=>'digit', 'class'=>'tl_text_4'),
                    ),
                    'height' => array
                    (
                        'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwHeight'],
                        'inputType'   => 'text',
                        'eval'        => array('rgxp'=>'digit', 'class'=>'tl_text_4'),
                    ),
                    'mode' => array
                    (
                        'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwMode'],
                        'inputType'   => 'select',
                        'options'     => $GLOBALS['TL_CROP'],
                        'reference'   => &$GLOBALS['TL_LANG']['MSC'],
                        'eval'        => array('style'=>'width:150px'),
                    ),
                    'watermark' => array
                    (
                        'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwWatermark'],
                        'inputType'   => 'text',
                        'eval'        => array('class'=>'tl_text_2'),
                        'wizard'      => array(array('tl_iso_config', 'filePicker')),
                    ),
                    'position' => array
                    (
                        'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwPosition'],
                        'inputType'   => 'select',
                        'options'     => array('tl', 'tc', 'tr', 'bl', 'bc', 'br', 'cc'),
                        'reference'   => $GLOBALS['TL_LANG']['tl_iso_config'],
                        'eval'        => array('style'=>'width:60px'),
                    ),
                ),
            ),
        ),
        'priceCalculateFactor' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor'],
            'exclude'                 => true,
            'default'				  => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
        ),
        'priceCalculateMode' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode'],
            'exclude'                 => true,
            'default'				  => 'mul',
            'inputType'               => 'radio',
            'options'				  => array('mul', 'div'),
            'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
        ),
        'priceRoundPrecision' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision'],
            'exclude'                 => true,
            'default'				  => '2',
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>1, 'rgpx'=>'digit', 'tl_class'=>'w50'),
        ),
        'priceRoundIncrement' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'				  => array('0.01', '0.05'),
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
        ),
        'cartMinSubtotal' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal'],
            'exclude'                 => true,
            'default'				  => '',
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>13, 'rgpx'=>'price', 'tl_class'=>'w50'),
        ),
        'currency' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currency'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'				  => &$GLOBALS['ISO_LANG']['CUR'],
            'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
        ),
        'currencySymbol' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'					  => array('submitOnChange'=>true, 'tl_class'=>'w50'),
        ),
        'currencySpace' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'					  => array('tl_class'=>'w50'),
        ),
        'currencyPosition' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'default'				  => 'left',
            'options'				  => array('left', 'right'),
            'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'					  => array('tl_class'=>'w50'),
        ),
        'currencyFormat' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'				  => array_keys($GLOBALS['ISO_NUM']),
            'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
        ),
        'currencyAutomator' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyAutomator'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'					  => array('submitOnChange'=>true, 'tl_class'=>'clr', 'helpwizard'=>true),
        ),
        'currencyOrigin' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyOrigin'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'				  => &$GLOBALS['ISO_LANG']['CUR'],
            'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
        ),
        'currencyProvider' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyProvider'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'				  => array('ecb.int', 'admin.ch'),
            'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
        ),
    )
);
