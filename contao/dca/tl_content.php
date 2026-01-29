<?php

use Isotope\Backend;
use Isotope\Backend\Config\Callback as ConfigCallback;
use Isotope\Backend\Module\Callback;
use Isotope\CoreBundle\Product\Category\CategoryScope;

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'iso_checkout_method';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'iso_enableLimit';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'iso_emptyMessage';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'iso_emptyFilter';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_productlist'] = '{type_legend},title,headline,type;{config_legend},numberOfItems,perPage,iso_category_scope,iso_filterQuery,iso_filterModules,iso_searchFields,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_link_primary,iso_jump_first,iso_addProductJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_use_quantity,iso_hide_list,iso_hide_empty,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_productreader'] = '{type_legend},title,headline,type;{config_legend},iso_use_quantity,iso_display404Page;{redirect_legend},iso_addProductJumpTo;{template_legend:hide},customTpl,iso_reader_layout,iso_gallery,iso_disable_options,iso_includeMessages,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_cart'] = '{type_legend},title,headline,type;{redirect_legend},iso_cart_jumpTo,iso_checkout_jumpTo;{template_legend},customTpl,iso_orderCollectionBy,iso_gallery,iso_continueShopping,iso_includeMessages,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_checkout'] = '{type_legend},title,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_notifications;{redirect_legend},iso_forward_review,iso_show_skipped,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_checkoutmember'] = '{type_legend},title,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_addToAddressbook,iso_notifications;{redirect_legend},iso_forward_review,iso_show_skipped,iso_checkout_skippable,orderCompleteJumpTo,iso_login_jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_checkoutguest'] = '{type_legend},title,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_notifications;{redirect_legend},iso_forward_review,iso_show_skipped,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_checkoutboth'] = '{type_legend},title,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_addToAddressbook,iso_notifications;{redirect_legend},iso_forward_review,iso_show_skipped,iso_checkout_skippable,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{iso_order_conditions_legend},iso_order_conditions;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_orderhistory'] = '{type_legend},title,headline,type;{config_legend},iso_config_ids,iso_orderdetails_module;{redirect_legend},jumpTo,iso_cart_jumpTo;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_orderdetails'] = '{type_legend},title,headline,type;{config_legend},iso_loginRequired;{redirect_legend:hide},iso_cart_jumpTo;{template_legend},customTpl,iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_configswitcher'] = '{type_legend},title,headline,type;{config_legend},iso_config_ids;{template_legend},customTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_productfilter'] = '{type_legend},title,headline,type;{config_legend},iso_category_scope,iso_filterQuery,iso_enableLimit,iso_filterFields,iso_filterHideSingle,iso_enableSearch,iso_sortingFields,iso_listingSortField,iso_listingSortDirection;{template_legend},customTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo,iso_link_primary;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_categoryfilter'] = '{type_legend},title,headline,type;{nav_legend},levelOffset,showLevel,showProtected,showHidden;{reference_legend:hide},defineRoot;{template_legend},customTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_addressbook'] = '{type_legend},title,headline,type;{config_legend},nc_notification;{template_legend},customTpl,memberTpl,iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_relatedproducts'] = '{type_legend},title,headline,type;{config_legend},iso_related_categories,numberOfItems,perPage,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_hide_empty,iso_use_quantity,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['iso_messages'] = '{type_legend},title,headline,type;{template_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID,space';

$GLOBALS['TL_DCA']['tl_content']['subpalettes']['iso_enableLimit'] = 'iso_perPage';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['iso_emptyMessage'] = 'iso_noProducts';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['iso_emptyFilter'] = 'iso_noFilter';

$GLOBALS['TL_DCA']['tl_content']['fields']['navigationTpl']['eval']['includeBlankOption'] = true;

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_list_layout'] = [
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_reader_layout'] = [
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_gallery'] = [
    'inputType' => 'select',
    'foreignKey' => 'tl_iso_gallery.name',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_collectionTpl'] = [
    'default' => 'iso_collection_default',
    'inputType' => 'select',
    'options_callback' => static fn () => Backend::getTemplates('iso_collection_'),
    'eval' => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_jump_first'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_link_primary'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_hide_list'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_hide_empty'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_disable_options'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_use_quantity'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_display404Page'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_checkout_method'] = [
    'inputType' => 'radio',
    'default' => 'member',
    'options' => ['member', 'guest', 'both'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['iso_checkout_method_ref'],
    'eval' => ['mandatory' => true, 'submitOnChange' => true],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_login_jumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_loginRequired'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_addProductJumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_config_id'] = [
    'inputType' => 'select',
    'foreignKey' => 'tl_iso_config.name',
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50'],
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_config_ids'] = [
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_iso_config.name',
    'eval' => ['multiple' => true, 'mandatory' => true, 'tl_class' => 'clr'],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_payment_modules'] = [
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_iso_payment.name',
    'options_callback' => [Callback::class, 'getPaymentModules'],
    'eval' => ['multiple' => true],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_shipping_modules'] = [
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_iso_shipping.name',
    'options_callback' => [Callback::class, 'getShippingModules'],
    'eval' => ['multiple' => true],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['orderCompleteJumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_forward_review'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_show_skipped'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_checkout_skippable'] = [
    'inputType' => 'checkbox',
    'options' => ['billing_address', 'shipping_address', 'payment_method', 'shipping_method'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['iso_checkout_skippable'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_order_conditions'] = [
    'inputType' => 'rowWizard',
    'fields' => [
        'form' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['iso_order_conditions']['form'],
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_form.title',
            'eval' => [
                'includeBlankOption' => true,
                'style' => 'width:300px',
                'chosen' => true,
            ],
        ],
        'step' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['iso_order_conditions']['step'],
            'inputType' => 'select',
            'eval' => [
                'decodeEntities' => true,
                'includeBlankOption' => true,
                'style' => 'width:150px',
            ],
        ],
        'position' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['iso_order_conditions']['position'],
            'default' => 'before',
            'inputType' => 'select',
            'options' => ['before', 'after'],
            'reference' => &$GLOBALS['TL_LANG']['tl_content']['iso_order_conditions']['position'],
            'eval' => [
                'style' => 'width:150px',
            ],
        ],
        'product_types' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['iso_order_conditions']['product_types'],
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_producttype.name',
            'eval' => [
                'multiple' => true,
                'chosen' => true,
                'style' => 'width:300px',
            ],
        ],
        'product_types_condition' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['iso_order_conditions']['product_types_condition'],
            'inputType' => 'select',
            'options' => ['oneAvailable', 'allAvailable', 'onlyAvailable'],
            'reference' => &$GLOBALS['TL_LANG']['tl_content']['iso_order_conditions']['product_types_condition'],
            'eval' => [
                'style' => 'width:300px',
            ],
        ],
    ],
    'eval' => [
        'decodeEntities' => true,
        'tl_class' => 'clr',
    ],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_addToAddressbook'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_orderCollectionBy'] = [
    'default' => 'asc_id',
    'inputType' => 'select',
    'options' => &$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
    'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_emptyMessage'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_noProducts'] = [
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_emptyFilter'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_noFilter'] = [
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_category_scope'] = [
    'inputType' => 'radio',
    'default' => 'current_category',
    'enum' => CategoryScope::class,
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['iso_category_scope_ref'],
    'explanation' => 'iso_category_scope',
    'eval' => ['tl_class' => 'clr w50', 'helpwizard' => true],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_filterQuery'] = [
    'inputType' => 'text',
    'explanation' => 'iso_filterQuery',
    'eval' => ['preserveTags' => true, 'tl_class' => 'w50', 'helpwizard' => true],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_filterModules'] = [
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_content.name',
    'options_callback' => [Callback::class, 'getFilterModules'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_filterFields'] = [
    'inputType' => 'checkboxWizard',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_filterHideSingle'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_enableSearch'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_searchFields'] = [
    'inputType' => 'checkbox',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_sortingFields'] = [
    'inputType' => 'checkboxWizard',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_enableLimit'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50 m12'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_perPage'] = [
    'default' => '8,12,32,64',
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 64, 'rgxp' => 'extnd', 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_cart_jumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_checkout_jumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'explanation' => 'jumpTo',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_listingSortField'] = [
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_listingSortDirection'] = [
    'default' => 'DESC',
    'inputType' => 'select',
    'options' => ['DESC', 'ASC'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['sortingDirection'],
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_buttons'] = [
    'inputType' => 'checkboxWizard',
    'default' => ['add_to_cart'],
    'reference' => &$GLOBALS['TL_LANG']['MSC']['buttonLabel'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_related_categories'] = [
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_iso_related_category.name',
    'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_includeMessages'] = [
    'inputType' => 'checkbox',
    'eval' => ['doNotCopy' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_continueShopping'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_notifications'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nc_notification'],
    'inputType' => 'select',
    //'options_callback' => ['NotificationCenter\tl_content', 'getNotificationChoices'],
    'eval' => ['multiple' => true, 'csv' => ',', 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'relation' => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_nc_notification'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_orderdetails_module'] = [
    'inputType' => 'select',
    'options_callback' => [ConfigCallback::class, 'getOrderDetailsModules'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'relation' => ['type' => 'hasOne', 'table' => 'tl_content'],
];

/*
 * Limit notification choices
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkout'] = ['iso_order_status_change'];
$GLOBALS['TL_DCA']['tl_content']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutmember'] = ['iso_order_status_change'];
$GLOBALS['TL_DCA']['tl_content']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutguest'] = ['iso_order_status_change'];
$GLOBALS['TL_DCA']['tl_content']['fields']['iso_notifications']['eval']['ncNotificationChoices']['iso_checkoutboth'] = ['iso_order_status_change'];
