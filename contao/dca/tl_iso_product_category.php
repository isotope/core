<?php

use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Isotope\Backend;
use Isotope\CoreBundle\DataContainer\TablePageId;

$GLOBALS['TL_DCA']['tl_iso_product_category'] = [
    'config' => [
        'dataContainer' => TablePageId::class,
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
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'panelLayout' => 'limit',
            'headerFields' => ['title', 'type'],
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
