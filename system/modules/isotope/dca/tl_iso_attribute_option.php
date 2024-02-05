<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Table tl_iso_attribute_option
 */
$GLOBALS['TL_DCA']['tl_iso_attribute_option'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Multilingual',
        'enableVersioning'          => true,
        'ptable'                    => 'tl_iso_attribute',
        'dynamicPtable'             => true,
        'onload_callback' => array
        (
            array('\Isotope\Backend\AttributeOption\Callback', 'initWrappers'),
            array('\Isotope\Backend\AttributeOption\Callback', 'checkPermission'),
        ),
        'onsubmit_callback' => array
        (
            array('\Isotope\Backend\AttributeOption\Callback', 'storeFieldName'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'ptable,pid' => 'index'
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 4,
            'fields'                => array('sorting'),
            'flag'                  => 1,
            'panelLayout'           => 'filter,search,limit',
            'headerFields'          => array('name', 'field_name', 'type', 'variant_option', 'customer_defined'),
            'child_record_callback' => array('\Isotope\Backend\AttributeOption\Callback', 'listRecords'),
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'              => 'act=edit',
                'icon'              => 'edit.svg'
            ),
            'copy' => array
            (
                'href'              => 'act=paste&amp;mode=copy',
                'icon'              => 'copy.svg'
            ),
            'cut' => array
            (
                'href'              => 'act=paste&amp;mode=cut',
                'icon'              => 'cut.svg'
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.svg',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
            ),
            'toggle' => array
            (
                'icon'              => 'visible.svg',
                'attributes'        => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'   => array('\Isotope\Backend\AttributeOption\Callback', 'toggleIcon')
            ),
            'show' => array
            (
                'href'              => 'act=show',
                'icon'              => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type'),
        'default'                   => '{label_legend},type',
        'option'                    => '{label_legend},type,isDefault,label;{price_legend:hide},price;{expert_legend:hide},cssClass;{publish_legend},published',
        'group'                     => '{label_legend},type,label;{expert_legend:hide},cssClass;{publish_legend},published',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'sorting' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'ptable' => array
        (
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'langPid' => array
        (
            'eval'                  => ['doNotShow' => true],
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'language' => array
        (
            'eval'                  => ['doNotShow' => true],
            'sql'                   => "varchar(5) NOT NULL default ''",
        ),
        'field_name' => array
        (
            'eval'                  => ['doNotShow' => true],
            'sql'                   => "varchar(30) NOT NULL default ''",
        ),
        'type' => array
        (
            'exclude'               => true,
            'default'               => 'option',
            'inputType'             => 'radio',
            'options'               => array('option', 'group'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['type'],
            'eval'                  => array('tl_class'=>'w50', 'doNotCopy'=>true, 'submitOnChange'=>true),
            'sql'                   => "varchar(8) NOT NULL default ''",
            'save_callback' => array
            (
                array('\Isotope\Backend\AttributeOption\Callback', 'saveType'),
            )
        ),
        'isDefault' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50 m12', 'doNotCopy'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'label' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'translatableFor'=>'*', 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'price' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_attribute_option'][(\Contao\Input::get('popup') ? 'price' : 'price_short')],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>16, 'rgxp'=>'discount', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
            'save_callback' => array(
                function($varValue) {
                    if ($varValue != '' && strpos($varValue, '.') === false && strpos($varValue, '%') === false) {
                        $varValue = number_format($varValue, 2, '.', '');

                        if ($varValue > 0) {
                            $varValue = '+' . $varValue;
                        }
                    }

                    return $varValue;
                }
            )
        ),
        'cssClass' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>64, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'published' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50', 'doNotCopy'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        )
    ),
);

// Using onload_callback is too late
if ('iso_products' === \Contao\Input::get('do')) {
    $GLOBALS['TL_DCA']['tl_iso_attribute_option']['config']['ptable'] = 'tl_iso_product';
    $GLOBALS['TL_DCA']['tl_iso_attribute_option']['list']['sorting']['filter'] = array(array('field_name=?', \Contao\Input::get('field')));
    $GLOBALS['TL_DCA']['tl_iso_attribute_option']['list']['sorting']['headerFields'] = array('name', 'type', 'alias', 'published');
}
