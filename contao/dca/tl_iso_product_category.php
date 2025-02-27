<?php

use Contao\BackendUser;
use Contao\Input;
use Contao\Message;
use Isotope\Backend;
use Isotope\Backend\ProductCategory\Callback;
use Isotope\CoreBundle\DataContainer\TablePageId;

/*
 * Table tl_iso_product_category
 */
$GLOBALS['TL_DCA']['tl_iso_product_category'] = [
    // Config
    'config' => [
        'dataContainer' => 'TablePageId',
        'ptable' => 'tl_page',
        'closed' => true,
        'notEditable' => true,
        'notCopyable' => true,
        'notDeletable' => true,
        'onload_callback' => [
            static function (): void {
                if ('' === Input::get('act') && BackendUser::getInstance()->hasAccess('modules', 'themes')) {
                    Message::addInfo($GLOBALS['TL_LANG']['tl_iso_product_category']['hint'] ?? '');
                }
            },
        ],
        'oncut_callback' => [
            [Backend::class, 'truncateProductCache'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'page_id' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['sorting'],
            'panelLayout' => 'limit',
            'headerFields' => ['title', 'type'],
            'child_record_callback' => [Callback::class, 'listRows'],
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'page_id' => [
            'foreignKey' => 'tl_page.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
    ],
];
