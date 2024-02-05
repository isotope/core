<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Group;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Isotope\Backend\Permission;
use Isotope\Model\Group;


class Callback extends Permission
{
    /**
     * Make the constructor public
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check access permissions
     *
     * @param object $dc
     */
    public function checkPermission($dc)
    {
        /** @var BackendUser $user */
        $user    = BackendUser::getInstance();
        $session = Session::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Load permissions in tl_iso_product
        if ('tl_iso_product' === $dc->table) {
            $arrGroups = $user->iso_groups;

            if (!\is_array($arrGroups) || empty($arrGroups)) {
                $GLOBALS['TL_DCA']['tl_iso_group']['list']['sorting']['filter'][] = array('id=?', 0);
            } else {
                $GLOBALS['TL_DCA']['tl_iso_group']['list']['sorting']['root'] = $arrGroups;
            }

            return;
        }

        // Set root IDs
        if (!\is_array($user->iso_groups) || empty($user->iso_groups)) {
            $root = array();
        } else {
            try {
                $root = $this->eliminateNestedPages($user->iso_groups, 'tl_iso_group');
            } catch (\Exception $e) {
                $root = array();
            }
        }

        $GLOBALS['TL_DCA']['tl_iso_group']['list']['sorting']['root'] = (empty($root) ? true : $root);

        if (\in_array('rootPaste', $user->iso_groupp, true)) {
            $GLOBALS['TL_DCA']['tl_iso_group']['list']['sorting']['rootPaste'] = true;
        }

        if (!\in_array('create', $user->iso_groupp, true)) {
            $GLOBALS['TL_DCA']['tl_iso_group']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_iso_group']['config']['notCopyable'] = true;
        }

        if (!\in_array('delete', $user->iso_groupp, true)) {
            $GLOBALS['TL_DCA']['tl_iso_group']['config']['notDeletable'] = true;
        }

        if (!$user->canEditFieldsOf('tl_iso_group')) {
            $GLOBALS['TL_DCA']['tl_iso_group']['config']['notEditable'] = true;
        }

        $root = array_merge($root, Database::getInstance()->getChildRecords($root, 'tl_iso_group'));

        // Check current action
        switch (Input::get('act')) {
            case 'create':
            case 'copy':
            case 'select':
            case 'paste':
                // Allow
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'edit':

                // Dynamically add the record to the user profile
                if (!\in_array(Input::get('id'), $root)
                    && $this->addNewRecordPermissions(Input::get('id'), 'tl_iso_group', 'iso_groups', 'iso_groupp')
                ) {
                    $root[] = Input::get('id');
                    $user->iso_groups = $root;
                }
            // No break;

            case 'delete':
            case 'show':
            case 'cut':
                if (!\in_array(Input::get('id'), $root)
                    || (
                        'delete' === Input::get('act')
                        && !$user->hasAccess('delete', 'iso_groupp')
                    )
                ) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' group ID "' . Input::get('id') . '"');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $sessionData = $session->getData();
                if ('deleteAll' === Input::get('act') && !$user->hasAccess('delete', 'iso_groupp')) {
                    $sessionData['CURRENT']['IDS'] = array();
                } else {
                    $sessionData['CURRENT']['IDS'] = array_intersect($sessionData['CURRENT']['IDS'], $root);
                }
                $session->setData($sessionData);
                break;

            default:
                if (\strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' groups');
                }
                break;
        }
    }

    /**
     * Add an image to each group in the tree
     *
     * @param array          $row
     * @param string         $label
     * @param string         $imageAttribute
     * @return string
     */
    public function addIcon($row, $label, DataContainer $dc = null, $imageAttribute = '')
    {
        $image = Image::getHtml('system/modules/isotope/assets/images/folder-network.png', '', $imageAttribute);

        if ('tl_iso_product' === $dc->table) {
            return $image . ' <span style="font-weight:bold">' . $label . '</span>';
        }

        $strProductType = '';

        if (($objProductType = Group::findByPk($row['id'])->getRelated('product_type')) !== null) {
            $strProductType = ' <span style="color:#b3b3b3; padding-left:3px;">[' . $objProductType->name . ']</span>';
        }

        return $image . ' ' . $label . $strProductType;
    }

    /**
     * Reassign products to no group when group is deleted
     *
     * @param object $dc
     */
    public function deleteGroup($dc)
    {
        $arrGroups   = Database::getInstance()->getChildRecords($dc->id, 'tl_iso_group');
        $arrGroups[] = $dc->id;

        Database::getInstance()->query(
            'UPDATE tl_iso_product SET gid=0 WHERE gid IN (' . implode(',', $arrGroups) . ')'
        );
    }

    /**
     * Disable edit button if user has no permission to edit any fields
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function editButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (!BackendUser::getInstance()->isAdmin
            && !BackendUser::getInstance()->canEditFieldsOf('tl_iso_group')
        ) {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Disable copy button if user has no permission to create groups
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function copyButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (!BackendUser::getInstance()->isAdmin
            && (
                !\is_array(BackendUser::getInstance()->iso_groupp)
                || !\in_array('create', BackendUser::getInstance()->iso_groupp, true)
            )
        ) {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Disable delete button if user has no permission to delete groups
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deleteButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (!BackendUser::getInstance()->isAdmin
            && (
                !\is_array(BackendUser::getInstance()->iso_groupp)
                || !\in_array('delete', BackendUser::getInstance()->iso_groupp, true)
            )
        ) {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }
}
