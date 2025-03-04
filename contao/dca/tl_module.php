<?php

use Contao\Controller;
use Contao\System;
use Isotope\Backend;
use Isotope\Backend\Module\Callback;
use Isotope\Backend\Module\CumulativeFields;
use Isotope\Backend\Module\OrderConditionFields;
use Isotope\Backend\Module\RangeFields;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Isotope\Model\Gallery;
use Isotope\Model\Payment;
use Isotope\Model\RelatedCategory;
use Isotope\Model\Shipping;

/*
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'iso_checkout_method';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'iso_enableLimit';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'iso_emptyMessage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'iso_emptyFilter';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'iso_tsdisplay';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'iso_tscheckout';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist'] = '{title_legend},name,headline,type;{config_legend},numberOfItems,perPage,iso_category_scope,iso_list_where,iso_newFilter,iso_filterModules,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_link_primary,iso_jump_first,iso_addProductJumpTo,iso_wishlistJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_use_quantity,iso_hide_list,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productvariantlist'] = '{title_legend},name,headline,type;{config_legend},numberOfItems,perPage,iso_category_scope,iso_list_where,iso_newFilter,iso_filterModules,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_link_primary,iso_jump_first,iso_addProductJumpTo,iso_wishlistJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_use_quantity,iso_hide_list,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productreader'] = '{title_legend},name,headline,type;{config_legend},iso_use_quantity,iso_display404Page;{redirect_legend},iso_addProductJumpTo,iso_wishlistJumpTo;{template_legend:hide},customTpl,iso_reader_layout,iso_gallery,iso_disable_options,iso_includeMessages,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_favorites'] = '{title_legend},name,headline,type;{config_legend},iso_moveFavorites;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cart'] = '{title_legend},name,headline,type;{redirect_legend},iso_cart_jumpTo,iso_checkout_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_continueShopping,iso_includeMessages,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkout'] = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_notifications;{redirect_legend},iso_forward_review,iso_show_skipped,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutmember'] = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_addToAddressbook,iso_notifications;{redirect_legend},iso_forward_review,iso_show_skipped,iso_checkout_skippable,orderCompleteJumpTo,iso_login_jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutguest'] = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_notifications;{redirect_legend},iso_forward_review,iso_show_skipped,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutboth'] = '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_addToAddressbook,iso_notifications;{redirect_legend},iso_forward_review,iso_show_skipped,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderhistory'] = '{title_legend},name,headline,type;{config_legend},iso_config_ids,iso_orderdetails_module;{redirect_legend},jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderdetails'] = '{title_legend},name,headline,type;{config_legend},iso_loginRequired;{redirect_legend:hide},iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_wishlistmanager'] = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_wishlistviewer'] = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_wishlistdetails'] = '{title_legend},name,headline,type;{redirect_legend:hide},iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_configswitcher'] = '{title_legend},name,headline,type;{config_legend},iso_config_ids;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productfilter'] = '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_newFilter,iso_enableLimit,iso_filterFields,iso_filterHideSingle,iso_searchFields,iso_searchExact,iso_searchAutocomplete,iso_sortingFields,iso_listingSortField,iso_listingSortDirection;{template_legend},iso_filterTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo,iso_link_primary;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cumulativefilter'] = '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_newFilter,iso_cumulativeFields,iso_filterHideSingle,iso_listingSortField,iso_listingSortDirection;{template_legend},customTpl,navigationTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_categoryfilter'] = '{title_legend},name,headline,type;{nav_legend},levelOffset,showLevel,showProtected,showHidden;{reference_legend:hide},defineRoot;{template_legend},customTpl,navigationTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_rangefilter'] = '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_newFilter,iso_rangeFields;{template_legend},customTpl,navigationTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_addressbook'] = '{title_legend},name,headline,type;{config_legend},nc_notification;{template_legend},customTpl,memberTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_relatedproducts'] = '{title_legend},name,headline,type;{config_legend},iso_related_categories,numberOfItems,perPage,iso_list_where,iso_newFilter,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_use_quantity,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_messages'] = '{title_legend},name,headline,type;{template_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_shipping_calculator'] = '{title_legend},name,headline,type;{config_legend},iso_shipping_modules;{template_legend:hide},customTpl,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cart_address'] = '{title_legend},name,headline,type;{config_legend},iso_address,iso_addressFields;{redirect_legend:hide},jumpTo;{template_legend:hide},memberTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_trustedshops'] = '{title_legend},name,headline,type;{config_legend},iso_tsid,iso_tsreviews,iso_tsdisplay,iso_tscheckout;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';

/*
 * Add subpalettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_enableLimit'] = 'iso_perPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_emptyMessage'] = 'iso_noProducts';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_emptyFilter'] = 'iso_noFilter';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_tsdisplay_standard'] = 'iso_tsyoffset';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_tsdisplay_custom'] = 'iso_tsdirection';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_tscheckout'] = 'iso_tsproducts';

$GLOBALS['TL_DCA']['tl_module']['fields']['navigationTpl']['eval']['includeBlankOption'] = true;

/*
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_layout'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => static fn () => Backend::getTemplates('iso_list_'),
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_reader_layout'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => static fn () => Backend::getTemplates('iso_reader_'),
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_gallery'] = [
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => Gallery::getTable().'.name',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_collectionTpl'] = [
    'exclude' => true,
    'default' => 'iso_collection_default',
    'inputType' => 'select',
    'options_callback' => static fn () => Backend::getTemplates('iso_collection_'),
    'eval' => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterTpl'] = [
    'exclude' => true,
    'default' => 'iso_filter_default',
    'inputType' => 'select',
    'options_callback' => [Callback::class, 'getFilterTemplates'],
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_jump_first'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_link_primary'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_hide_list'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_disable_options'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_use_quantity'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_display404Page'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_method'] = [
    'exclude' => true,
    'inputType' => 'radio',
    'default' => 'member',
    'options' => ['member', 'guest', 'both'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref'],
    'eval' => ['mandatory' => true, 'submitOnChange' => true],
    'sql' => "varchar(10) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_login_jumpTo'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_loginRequired'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addProductJumpTo'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_wishlistJumpTo'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_config_id'] = [
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => Config::getTable().'.name',
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_config_ids'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => Config::getTable().'.name',
    'eval' => ['multiple' => true, 'mandatory' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_payment_modules'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => Payment::getTable().'.name',
    'options_callback' => [Callback::class, 'getPaymentModules'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_shipping_modules'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => Shipping::getTable().'.name',
    'options_callback' => [Callback::class, 'getShippingModules'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['orderCompleteJumpTo'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_forward_review'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_show_skipped'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_skippable'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['billing_address', 'shipping_address', 'payment_method', 'shipping_method'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_skippable'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_order_conditions'] = [
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'eval' => [
        'decodeEntities' => true,
        'tl_class' => 'clr',
        'disableSorting' => true,
        'columnsCallback' => [OrderConditionFields::class, 'getColumns'],
    ],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addToAddressbook'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
    'eval' => ['tl_class' => 'w50 m12'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_orderCollectionBy'] = [
    'exclude' => true,
    'default' => 'asc_id',
    'inputType' => 'select',
    'options' => &$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
    'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_emptyMessage'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_noProducts'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_emptyFilter'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_noFilter'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_category_scope'] = [
    'exclude' => true,
    'inputType' => 'radio',
    'default' => 'current_category',
    'options' => ['current_category', 'current_and_first_child', 'current_and_all_children', 'parent', 'product', 'article', 'global'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref'],
    'explanation' => 'iso_category_scope',
    'eval' => ['tl_class' => 'clr w50 w50h', 'helpwizard' => true],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_where'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['preserveTags' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterModules'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_module.name',
    'options_callback' => [Callback::class, 'getFilterModules'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterFields'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options_callback' => [Callback::class, 'getFilterFields'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cumulativeFields'] = [
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'eval' => [
        'mandatory' => true,
        'dragAndDrop' => true,
        'tl_class' => 'clr',
        'columnsCallback' => [CumulativeFields::class, 'getColumns'],
    ],
    'sql' => 'blob NULL',
    'save_callback' => [
        [CumulativeFields::class, 'validateConfiguration'],
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_rangeFields'] = [
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'eval' => [
        'mandatory' => true,
        'dragAndDrop' => true,
        'tl_class' => 'clr',
        'columnsCallback' => [RangeFields::class, 'getColumns'],
    ],
    'sql' => 'blob NULL',
    'save_callback' => [
        [RangeFields::class, 'validateConfiguration'],
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_newFilter'] = [
    'exclude' => true,
    'inputType' => 'select',
    'default' => 'show_all',
    'options' => ['show_all', 'show_new', 'show_old'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_newFilter'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(8) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterHideSingle'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchFields'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options_callback' => [Callback::class, 'getSearchFields'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchExact'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => [Callback::class, 'getSearchFields'],
    'eval' => ['multiple' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchAutocomplete'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => [Callback::class, 'getAutocompleteFields'],
    'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_sortingFields'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options_callback' => [Callback::class, 'getSortingFields'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_enableLimit'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_perPage'] = [
    'exclude' => true,
    'default' => '8,12,32,64',
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 64, 'rgxp' => 'extnd', 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cart_jumpTo'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_jumpTo'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_listingSortField'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => [Callback::class, 'getSortingFields'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'clr w50'],
    'sql' => "varchar(255) NOT NULL default ''",
    'save_callback' => [
        [Backend::class, 'truncateProductCache'],
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_listingSortDirection'] = [
    'exclude' => true,
    'default' => 'DESC',
    'inputType' => 'select',
    'options' => ['DESC', 'ASC'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['sortingDirection'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(8) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_buttons'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'default' => ['add_to_cart'],
    'options_callback' => [Callback::class, 'getButtons'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_related_categories'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => RelatedCategory::getTable().'.name',
    'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_includeMessages'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['doNotCopy' => true, 'tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_continueShopping'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_moveFavorites'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_address'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['billing', 'shipping'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_address'],
    'eval' => ['mandatory' => true, 'multiple' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addressFields'] = [
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options_callback' => static function () {
        Controller::loadDataContainer(Address::getTable());
        System::loadLanguageFile(Address::getTable());

        $arrOptions = [];
        $arrDCA = &$GLOBALS['TL_DCA'][Address::getTable()]['fields'];

        foreach ($arrDCA as $k => $arrField) {
            if (!$arrField['eval']['feEditable']) {
                continue;
            }

            $arrOptions[$k] = $arrField['label'][0];
        }

        return $arrOptions;
    },
    'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_productcache'] = [
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nc_notification'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['NotificationCenter\tl_module', 'getNotificationChoices'],
    'eval' => ['multiple' => true, 'csv' => ',', 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_nc_notification'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_orderdetails_module'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => [Backend\Config\Callback::class, 'getOrderDetailsModules'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'table' => 'tl_module'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsid'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsreviews'] = [
    'exclude' => true,
    'default' => '1',
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsdisplay'] = [
    'exclude' => true,
    'default' => 'standard',
    'inputType' => 'select',
    'options' => ['standard', 'custom'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_tsdisplay'],
    'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(8) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsdirection'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['topLeft', 'topRight', 'bottomLeft', 'bottomRight'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_tsdirection'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsyoffset'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql' => "int(10) NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tscheckout'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_tsproducts'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

/*
 * Limit notification choices
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkout'] = ['iso_order_status_change'];
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutmember'] = ['iso_order_status_change'];
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutguest'] = ['iso_order_status_change'];
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutboth'] = ['iso_order_status_change'];
