<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
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
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist']              = '{title_legend},name,headline,type;{config_legend},numberOfItems,perPage,iso_category_scope,iso_list_where,iso_newFilter,iso_filterModules,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo,iso_wishlistJumpTo,iso_jump_first;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_hide_list,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productvariantlist']       = '{title_legend},name,headline,type;{config_legend},numberOfItems,perPage,iso_category_scope,iso_list_where,iso_newFilter,iso_filterModules,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo,iso_wishlistJumpTo,iso_jump_first;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_hide_list,,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productreader']            = '{title_legend},name,headline,type;{config_legend},iso_use_quantity,iso_display404Page;{redirect_legend},iso_addProductJumpTo,iso_wishlistJumpTo;{template_legend:hide},customTpl,iso_reader_layout,iso_gallery,iso_includeMessages,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_favorites']                = '{title_legend},name,headline,type;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cart']                     = '{title_legend},name,headline,type;{redirect_legend},iso_cart_jumpTo,iso_checkout_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_continueShopping,iso_includeMessages,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkout']                 = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,nc_notification;{redirect_legend},iso_forward_review,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions,tableless,iso_order_conditions_position;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutmember']           = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_addToAddressbook,nc_notification;{redirect_legend},iso_forward_review,iso_checkout_skippable,orderCompleteJumpTo,iso_login_jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions,tableless,iso_order_conditions_position;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutguest']            = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,nc_notification;{redirect_legend},iso_forward_review,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions,tableless,iso_order_conditions_position;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutboth']             = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_addToAddressbook,nc_notification;{redirect_legend},iso_forward_review,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,tableless,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions,iso_order_conditions_position;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderhistory']             = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderdetails']             = '{title_legend},name,headline,type;{config_legend},iso_loginRequired;{redirect_legend:hide},iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_wishlists']                = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_wishlist']                 = '{title_legend},name,headline,type;{config_legend},iso_loginRequired;{redirect_legend:hide},iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_configswitcher']           = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productfilter']            = '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_newFilter,iso_enableLimit,iso_filterFields,iso_filterHideSingle,iso_searchFields,iso_searchAutocomplete,iso_sortingFields,iso_listingSortField,iso_listingSortDirection;{template_legend},customTpl,iso_filterTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cumulativefilter']         = '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_newFilter,iso_cumulativeFields,iso_filterHideSingle;{template_legend},customTpl,navigationTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_addressbook']              = '{title_legend},name,headline,type;{config_legend},nc_notification;{template_legend},customTpl,memberTpl,tableless,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_relatedproducts']          = '{title_legend},name,headline,type;{config_legend},iso_related_categories,numberOfItems,perPage,iso_list_where,iso_newFilter,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_includeMessages,iso_emptyMessage,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_messages']                 = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_shipping_calculator']      = '{title_legend},name,headline,type;{config_legend},iso_shipping_modules;{template_legend:hide},customTpl,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cart_address']             = '{title_legend},name,headline,type;{config_legend},iso_address,iso_addressFields;{redirect_legend:hide},jumpTo;{template_legend:hide},memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Add subpalettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_enableLimit']       = 'iso_perPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_emptyMessage']      = 'iso_noProducts';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_emptyFilter']       = 'iso_noFilter';


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_layout'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_list_layout'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_gallery'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'foreignKey'                => \Isotope\Model\Gallery::getTable().'.name',
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_collectionTpl'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_collectionTpl'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_filterTpl'],
    'exclude'                   => true,
    'default'                   => 'iso_filter_default',
    'inputType'                 => 'select',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getFilterTemplates'),
    'eval'                      => array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_jump_first'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_jump_first'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_hide_list'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_hide_list'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_use_quantity'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_display404Page'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_display404Page'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_method'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_login_jumpTo'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_loginRequired'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addProductJumpTo'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_wishlistJumpTo'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_cols'],
    'exclude'                   => true,
    'default'                   => 1,
    'inputType'                 => 'text',
    'eval'                      => array('maxlength'=>1, 'rgxp'=>'digit', 'tl_class'=>'w50'),
    'sql'                       => "int(1) unsigned NOT NULL default '1'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_config_id'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_config_id'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'foreignKey'                => \Isotope\Model\Config::getTable().'.name',
    'eval'                      => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_config_ids'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_config_ids'],
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'foreignKey'                => \Isotope\Model\Config::getTable().'.name',
    'eval'                      => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL',
    'relation'                  => array('type'=>'hasMany', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_payment_modules'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_payment_modules'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_shipping_modules'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['orderCompleteJumpTo'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_forward_review'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_skippable'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_skippable'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'options'                   => ['billing_address', 'shipping_address', 'payment_method', 'shipping_method'],
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_skippable'],
    'eval'                      => ['multiple' => true],
    'sql'                       => "text NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_order_conditions'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'foreignKey'                => 'tl_form.title',
    'eval'                      => array('includeBlankOption'=>true, 'tl_class'=>'clr w50', 'chosen'=>true),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_order_conditions_position'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position'],
    'exclude'                   => true,
    'inputType'                 => 'radio',
    'options'                   => array('top', 'before', 'after'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position'],
    'eval'                      => array('tl_class'=>'w50 w50h'),
    'sql'                       => "varchar(6) NOT NULL default 'after'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addToAddressbook'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_addToAddressbook'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'sql'                       => "char(1) NOT NULL default ''",
    'eval'                      => array('tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_orderCollectionBy'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy'],
    'exclude'                   => true,
    'default'                   => 'asc_id',
    'inputType'                 => 'select',
    'options'                   => $GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
    'eval'                      => array('mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(16) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_emptyMessage'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_emptyMessage'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'clr w50'),
    'sql'                       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_noProducts'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_noProducts'],
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('maxlength'=>255, 'tl_class'=>'clr long'),
    'sql'                       => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_emptyFilter'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_emptyFilter'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'clr'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_noFilter'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_noFilter'],
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('maxlength'=>255, 'tl_class'=>'clr long'),
    'sql'                       => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_category_scope'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope'],
    'exclude'                   => true,
    'inputType'                 => 'radio',
    'default'                   => 'current_category',
    'options'                   => array('current_category', 'current_and_first_child', 'current_and_all_children', 'parent', 'product', 'article', 'global'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref'],
    'eval'                      => array('tl_class'=>'clr w50 w50h', 'helpwizard'=>true),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_where'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_list_where'],
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('preserveTags'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                       => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterModules'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_filterModules'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_filterFields'],
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getFilterFields'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cumulativeFields'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields'],
    'exclude'                   => true,
    'inputType'                 => 'multiColumnWizard',
    'eval'                      => array(
        'mandatory' => true,
        'tl_class'  => 'clr',
        'columnsCallback' => array('Isotope\Backend\Module\CumulativeFields', 'getColumns')
    ),
    'sql'                       => 'blob NULL',
    'save_callback' => array(
        array('Isotope\Backend\Module\CumulativeFields', 'validateConfiguration')
    )
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_newFilter'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_newFilter'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_filterHideSingle'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchFields'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_searchFields'],
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getSearchFields'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchAutocomplete'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_searchAutocomplete'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getAutocompleteFields'),
    'eval'                      => array('tl_class'=>'w50', 'includeBlankOption'=>true),
    'sql'                       => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_sortingFields'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_sortingFields'],
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getSortingFields'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_enableLimit'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'clr w50 m12'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_perPage'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_perPage'],
    'exclude'                   => true,
    'default'                   => '8,12,32,64',
    'inputType'                 => 'text',
    'eval'                      => array('mandatory'=>true, 'maxlength'=>64, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
    'sql'                       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cart_jumpTo'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_cart_jumpTo'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_jumpTo'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'],
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
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_buttons'],
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'default'                   => array('add_to_cart'),
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getButtons'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_related_categories'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_related_categories'],
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'foreignKey'                => \Isotope\Model\RelatedCategory::getTable().'.name',
    'eval'                      => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL',
    'relation'                  => array('type'=>'hasMany', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_includeMessages'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_includeMessages'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('doNotCopy'=>true, 'tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_continueShopping'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_continueShopping'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_address'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_address'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'options'                   => array('billing', 'shipping'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module']['iso_address'],
    'eval'                      => array('mandatory'=>true, 'multiple'=>true),
    'sql'                       => 'blob NULL',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addressFields'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_addressFields'],
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'options_callback'          => function() {
        \Controller::loadDataContainer(\Isotope\Model\Address::getTable());
        \System::loadLanguageFile(\Isotope\Model\Address::getTable());

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


/**
 * Limit notification choices
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['iso_checkout']         = array('iso_order_status_change');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['iso_checkoutmember']   = array('iso_order_status_change');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['iso_checkoutguest']    = array('iso_order_status_change');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['iso_checkoutboth']     = array('iso_order_status_change');
