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
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]               = 'iso_checkout_method';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]               = 'iso_enableLimit';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]               = 'iso_emptyMessage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]               = 'iso_emptyFilter';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]               = 'iso_tsdisplay';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]               = 'iso_tscheckout';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist']              = '{title_legend},name,headline,type;{config_legend},numberOfItems,perPage,iso_category_scope,iso_list_where,iso_newFilter,iso_filterModules,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_link_primary,iso_jump_first,iso_addProductJumpTo,iso_wishlistJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_hide_list,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productvariantlist']       = '{title_legend},name,headline,type;{config_legend},numberOfItems,perPage,iso_category_scope,iso_list_where,iso_newFilter,iso_filterModules,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_link_primary,iso_jump_first,iso_addProductJumpTo,iso_wishlistJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_hide_list,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productreader']            = '{title_legend},name,headline,type;{config_legend},iso_use_quantity,iso_display404Page;{redirect_legend},iso_addProductJumpTo,iso_wishlistJumpTo;{template_legend:hide},customTpl,iso_reader_layout,iso_gallery,iso_disable_options,iso_includeMessages,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_favorites']                = '{title_legend},name,headline,type;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cart']                     = '{title_legend},name,headline,type;{redirect_legend},iso_cart_jumpTo,iso_checkout_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_continueShopping,iso_includeMessages,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkout']                 = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_notifications;{redirect_legend},iso_forward_review,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutmember']           = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_addToAddressbook,iso_notifications;{redirect_legend},iso_forward_review,iso_checkout_skippable,orderCompleteJumpTo,iso_login_jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutguest']            = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_notifications;{redirect_legend},iso_forward_review,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutboth']             = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_addToAddressbook,iso_notifications;{redirect_legend},iso_forward_review,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,tableless,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderhistory']             = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderdetails']             = '{title_legend},name,headline,type;{config_legend},iso_loginRequired;{redirect_legend:hide},iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_wishlistmanager']          = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_wishlistviewer']           = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_wishlistdetails']          = '{title_legend},name,headline,type;{redirect_legend:hide},iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_configswitcher']           = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productfilter']            = '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_newFilter,iso_enableLimit,iso_filterFields,iso_filterHideSingle,iso_searchFields,iso_searchExact,iso_searchAutocomplete,iso_sortingFields,iso_listingSortField,iso_listingSortDirection;{template_legend},iso_filterTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo,iso_link_primary;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cumulativefilter']         = '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_newFilter,iso_cumulativeFields,iso_filterHideSingle;{template_legend},customTpl,navigationTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_categoryfilter']           = '{title_legend},name,headline,type;{nav_legend},levelOffset,showLevel,showProtected,showHidden;{reference_legend:hide},defineRoot;{template_legend},customTpl,navigationTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_rangefilter']              = '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_newFilter,iso_rangeFields;{template_legend},customTpl,navigationTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_addressbook']              = '{title_legend},name,headline,type;{config_legend},nc_notification;{template_legend},customTpl,memberTpl,tableless,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_relatedproducts']          = '{title_legend},name,headline,type;{config_legend},iso_related_categories,numberOfItems,perPage,iso_list_where,iso_newFilter,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_messages']                 = '{title_legend},name,headline,type;{template_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_shipping_calculator']      = '{title_legend},name,headline,type;{config_legend},iso_shipping_modules;{template_legend:hide},customTpl,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cart_address']             = '{title_legend},name,headline,type;{config_legend},iso_address,iso_addressFields;{redirect_legend:hide},jumpTo;{template_legend:hide},memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_trustedshops']             = '{title_legend},name,headline,type;{config_legend},iso_tsid,iso_tsreviews,iso_tsdisplay,iso_tscheckout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Add subpalettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_enableLimit']       = 'iso_perPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_emptyMessage']      = 'iso_noProducts';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_emptyFilter']       = 'iso_noFilter';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_tsdisplay_standard'] = 'iso_tsyoffset';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_tsdisplay_custom']  = 'iso_tsdirection';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_tscheckout']        = 'iso_tsproducts';


$GLOBALS['TL_DCA']['tl_module']['fields']['navigationTpl']['eval']['includeBlankOption'] = true;


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_layout'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => function() {
        return \Isotope\Backend::getTemplates('iso_list_');
    },
    'eval'                      => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_reader_layout'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => function() {
        return \Isotope\Backend::getTemplates('iso_reader_');
    },
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_gallery'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'foreignKey'                => \Isotope\Model\Gallery::getTable().'.name',
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_collectionTpl'] = array
(
    'exclude'                   => true,
    'default'                   => 'iso_collection_default',
    'inputType'                 => 'select',
    'options_callback'          => function() {
        return \Isotope\Backend::getTemplates('iso_collection_');
    },
    'eval'                      => array('mandatory'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterTpl'] = array
(
    'exclude'                   => true,
    'default'                   => 'iso_filter_default',
    'inputType'                 => 'select',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getFilterTemplates'),
    'eval'                      => array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_jump_first'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_link_primary'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_hide_list'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_disable_options'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_use_quantity'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_display404Page'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_method'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'radio',
    'default'                   => 'member',
    'options'                   => array('member', 'guest', 'both'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref'],
    'eval'                      => array('mandatory'=>true, 'submitOnChange'=>true),
    'sql'                       => "varchar(10) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_login_jumpTo'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'pageTree',
    'foreignKey'                => 'tl_page.title',
    'eval'                      => array('fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
    'explanation'               => 'jumpTo',
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_loginRequired'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addProductJumpTo'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'pageTree',
    'foreignKey'                => 'tl_page.title',
    'eval'                      => array('fieldType'=>'radio', 'tl_class'=>'clr'),
    'explanation'               => 'jumpTo',
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_wishlistJumpTo'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'pageTree',
    'foreignKey'                => 'tl_page.title',
    'eval'                      => array('fieldType'=>'radio', 'tl_class'=>'clr'),
    'explanation'               => 'jumpTo',
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cols'] = array
(
    'exclude'                   => true,
    'default'                   => 1,
    'inputType'                 => 'text',
    'eval'                      => array('maxlength'=>1, 'rgxp'=>'digit', 'tl_class'=>'w50'),
    'sql'                       => "int(1) unsigned NOT NULL default '1'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_config_id'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'foreignKey'                => \Isotope\Model\Config::getTable().'.name',
    'eval'                      => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_config_ids'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'foreignKey'                => \Isotope\Model\Config::getTable().'.name',
    'eval'                      => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL',
    'relation'                  => array('type'=>'hasMany', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_payment_modules'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'foreignKey'                => \Isotope\Model\Payment::getTable().'.name',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getPaymentModules'),
    'eval'                      => array('multiple'=>true),
    'sql'                       => 'blob NULL',
    'relation'                  => array('type'=>'hasMany', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_shipping_modules'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'foreignKey'                => \Isotope\Model\Shipping::getTable().'.name',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getShippingModules'),
    'eval'                      => array('multiple'=>true),
    'sql'                       => 'blob NULL',
    'relation'                  => array('type'=>'hasMany', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['orderCompleteJumpTo'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'pageTree',
    'foreignKey'                => 'tl_page.title',
    'eval'                      => array('mandatory'=>true, 'fieldType'=>'radio', 'tl_class'=>'clr'),
    'explanation'               => 'jumpTo',
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_forward_review'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_skippable'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'options'                   => ['billing_address', 'shipping_address', 'payment_method', 'shipping_method'],
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_skippable'],
    'eval'                      => ['multiple' => true],
    'sql'                       => "text NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_order_conditions'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'multiColumnWizard',
    'eval'                      => array(
        'decodeEntities' => true,
        'tl_class'  => 'clr',
        'disableSorting' => true,
        'columnsCallback' => array('Isotope\Backend\Module\OrderConditionFields', 'getColumns')
    ),
    'sql'                       => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addToAddressbook'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'sql'                       => "char(1) NOT NULL default ''",
    'eval'                      => array('tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_orderCollectionBy'] = array
(
    'exclude'                   => true,
    'default'                   => 'asc_id',
    'inputType'                 => 'select',
    'options'                   => &$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
    'eval'                      => array('mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(16) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_emptyMessage'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'clr w50'),
    'sql'                       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_noProducts'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('maxlength'=>255, 'tl_class'=>'clr long'),
    'sql'                       => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_emptyFilter'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'clr'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_noFilter'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('maxlength'=>255, 'tl_class'=>'clr long'),
    'sql'                       => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_category_scope'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'radio',
    'default'                   => 'current_category',
    'options'                   => array('current_category', 'current_and_first_child', 'current_and_all_children', 'parent', 'product', 'article', 'global'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref'],
    'explanation'               => 'iso_category_scope',
    'eval'                      => array('tl_class'=>'clr w50 w50h', 'helpwizard'=>true),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_where'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('preserveTags'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                       => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterModules'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'foreignKey'                => 'tl_module.name',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getFilterModules'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL',
    'relation'                  => array('type'=>'hasMany', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterFields'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getFilterFields'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cumulativeFields'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'multiColumnWizard',
    'eval'                      => array(
        'mandatory' => true,
        'dragAndDrop' => true,
        'tl_class'  => 'clr',
        'columnsCallback' => array('Isotope\Backend\Module\CumulativeFields', 'getColumns')
    ),
    'sql'                       => 'blob NULL',
    'save_callback' => array(
        array('Isotope\Backend\Module\CumulativeFields', 'validateConfiguration')
    )
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_rangeFields'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'multiColumnWizard',
    'eval'                      => array(
        'mandatory' => true,
        'dragAndDrop' => true,
        'tl_class'  => 'clr',
        'columnsCallback' => array('Isotope\Backend\Module\RangeFields', 'getColumns')
    ),
    'sql'                       => 'blob NULL',
    'save_callback' => array(
        array('Isotope\Backend\Module\RangeFields', 'validateConfiguration')
    )
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_newFilter'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'default'                   => 'show_all',
    'options'                   => array('show_all', 'show_new', 'show_old'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_newFilter'],
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "varchar(8) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterHideSingle'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchFields'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getSearchFields'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
    'sql'                       => 'blob NULL',
);


$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchExact'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getSearchFields'),
    'eval'                      => array('multiple'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchAutocomplete'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getAutocompleteFields'),
    'eval'                      => array('tl_class'=>'w50', 'includeBlankOption'=>true),
    'sql'                       => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_sortingFields'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getSortingFields'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_enableLimit'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'clr w50 m12'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_perPage'] = array
(
    'exclude'                   => true,
    'default'                   => '8,12,32,64',
    'inputType'                 => 'text',
    'eval'                      => array('mandatory'=>true, 'maxlength'=>64, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cart_jumpTo'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'pageTree',
    'foreignKey'                => 'tl_page.title',
    'eval'                      => array('fieldType'=>'radio', 'tl_class'=>'clr'),
    'explanation'               => 'jumpTo',
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_jumpTo'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'pageTree',
    'foreignKey'                => 'tl_page.title',
    'eval'                      => array('fieldType'=>'radio', 'tl_class'=>'clr'),
    'explanation'               => 'jumpTo',
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_listingSortField'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getSortingFields'),
    'eval'                      => array('includeBlankOption'=>true, 'tl_class'=>'clr w50'),
    'sql'                       => "varchar(255) NOT NULL default ''",
    'save_callback'             => array
    (
        array('Isotope\Backend', 'truncateProductCache'),
    ),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_listingSortDirection'] = array
(
    'exclude'                   => true,
    'default'                   => 'DESC',
    'inputType'                 => 'select',
    'options'                   => array('DESC', 'ASC'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['sortingDirection'],
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "varchar(8) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_buttons'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'default'                   => array('add_to_cart'),
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getButtons'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_related_categories'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'foreignKey'                => \Isotope\Model\RelatedCategory::getTable().'.name',
    'eval'                      => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL',
    'relation'                  => array('type'=>'hasMany', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_includeMessages'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('doNotCopy'=>true, 'tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_continueShopping'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_address'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'options'                   => array('billing', 'shipping'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_address'],
    'eval'                      => array('mandatory'=>true, 'multiple'=>true),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addressFields'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'options_callback'          => function() {
        \Contao\Controller::loadDataContainer(\Isotope\Model\Address::getTable());
        \Contao\System::loadLanguageFile(\Isotope\Model\Address::getTable());

        $arrOptions = array();
        $arrDCA = &$GLOBALS['TL_DCA'][\Isotope\Model\Address::getTable()]['fields'];

        foreach ($arrDCA as $k => $arrField) {
            if (!$arrField['eval']['feEditable']) {
                continue;
            }

            $arrOptions[$k] = $arrField['label'][0];
        }

        return $arrOptions;
    },
    'eval'                      => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL'
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_productcache'] = array
(
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('NotificationCenter\tl_module', 'getNotificationChoices'),
    'eval'                      => array('multiple'=>true, 'csv'=>',', 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(255) NOT NULL default ''",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy', 'table'=>'tl_nc_notification'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsid'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('mandatory'=>true, 'maxlength'=>64, 'tl_class'=>'w50'),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsreviews'] = array
(
    'exclude'                   => true,
    'default'                   => '1',
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50 m12'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsdisplay'] = array
(
    'exclude'                   => true,
    'default'                   => 'standard',
    'inputType'                 => 'select',
    'options'                   => array('standard', 'custom'),
    'reference'                 => $GLOBALS['TL_LANG']['tl_module']['iso_tsdisplay'],
    'eval'                      => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(8) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsdirection'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options'                   => array('topLeft', 'topRight', 'bottomLeft', 'bottomRight'),
    'reference'                 => $GLOBALS['TL_LANG']['tl_module']['iso_tsdirection'],
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "varchar(16) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsyoffset'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('mandatory'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'),
    'sql'                       => "int(10) NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tscheckout'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'clr w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsproducts'] = array
(
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);


/**
 * Limit notification choices
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkout']         = array('iso_order_status_change');
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutmember']   = array('iso_order_status_change');
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutguest']    = array('iso_order_status_change');
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutboth']     = array('iso_order_status_change');
