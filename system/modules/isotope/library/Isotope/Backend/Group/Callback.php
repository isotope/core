<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Group;

use Isotope\Model\Group;


class Callback extends \Backend
{

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
        /** @type \BackendUser $user */
        $user    = \BackendUser::getInstance();
        $session = \Session::getInstance();
        $db      = \Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Load permissions in tl_iso_product
        if ($dc->table == 'tl_iso_product' || stripos(\Environment::get('request'), 'group.php') !== false) {
            $arrGroups = $user->iso_groups;

            if (!is_array($arrGroups) || empty($arrGroups)) {
                $GLOBALS['TL_DCA']['tl_iso_group']['list']['sorting']['filter'][] = array('id=?', 0);
            } else {
                $GLOBALS['TL_DCA']['tl_iso_group']['list']['sorting']['root'] = $arrGroups;
            }

            return;
        }

        if (!is_array($user->iso_groupp) || empty($user->iso_groupp)) {
            \System::log('Unallowed access to product groups!', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        // Set root IDs
        if (!is_array($user->iso_groups) || empty($user->iso_groups)) {
            $root = array();
        } else {
            try {
                $root = $this->eliminateNestedPages($user->iso_groups, 'tl_iso_group');
            } catch (\Exception $e) {
                $root = array();
            }
        }

        $GLOBALS['TL_DCA']['tl_iso_group']['list']['sorting']['root'] = (empty($root) ? true : $root);

        if (in_array('rootPaste', $user->iso_groupp)) {
            $GLOBALS['TL_DCA']['tl_iso_group']['list']['sorting']['rootPaste'] = true;
        }

        // Check permissions to add product group
        if (!in_array('create', $user->iso_groupp)) {
            $GLOBALS['TL_DCA']['tl_iso_group']['config']['closed'] = true;
        }

        // Check current action
        switch (\Input::get('act')) {
            case 'create':
            case 'select':
            case 'paste':
                // Allow
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'edit':

                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root)) {
                    $arrNew = $session->get('new_records');

                    if (is_array($arrNew['tl_iso_group']) && in_array(\Input::get('id'), $arrNew['tl_iso_group'])) {
                        // Add permissions on user level

                        if ($user->inherit == 'custom'
                            || !$user->groups[0]
                        ) {
                            $objUser = $db->prepare("
                                SELECT iso_groups, iso_groupp FROM tl_user WHERE id=?
                            ")->limit(1)->execute($user->id);

                            $arrPermissions = deserialize($objUser->iso_groupp);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objUser->iso_groups);
                                $arrAccess[] = \Input::get('id');

                                $db->prepare(
                                    "UPDATE tl_user SET iso_groups=? WHERE id=?"
                                )->execute(serialize($arrAccess), $user->id);
                            }

                        } elseif ($user->groups[0] > 0) {
                            // Add permissions on group level

                            $objGroup = $db->prepare("
                                SELECT iso_groups, iso_groupp FROM tl_user_group WHERE id=?
                            ")->limit(1)->execute($user->groups[0]);

                            $arrPermissions = deserialize($objGroup->iso_groupp);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objGroup->iso_groups);
                                $arrAccess[] = \Input::get('id');

                                $db->prepare("
                                    UPDATE tl_user_group SET iso_groups=? WHERE id=?
                                ")->execute(serialize($arrAccess), $user->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[]           = \Input::get('id');
                        $user->iso_groups = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root)
                    || (
                        \Input::get('act') == 'delete'
                        && !$user->hasAccess('delete', 'iso_groupp')
                    )
                ) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' group ID "' . \Input::get('id') . '"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $sessionData = $session->getData();
                if (\Input::get('act') == 'deleteAll' && !$user->hasAccess('delete', 'iso_groupp')) {
                    $sessionData['CURRENT']['IDS'] = array();
                } else {
                    $sessionData['CURRENT']['IDS'] = array_intersect($sessionData['CURRENT']['IDS'], $root);
                }
                $session->setData($sessionData);
                break;

            default:
                if (strlen(\Input::get('act'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' groups', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    /**
     * Add an image to each group in the tree
     *
     * @param array          $row
     * @param string         $label
     * @param \DataContainer $dc
     * @param string         $imageAttribute
     *
     * @return string
     */
    public function addIcon($row, $label, \DataContainer $dc = null, $imageAttribute = '')
    {
        $image = \Image::getHtml('system/modules/isotope/assets/images/folder-network.png', '', $imageAttribute);

        if ($dc->table == 'tl_iso_product') {
            return $image . ' <span style="font-weight:bold">' . $label . '</span>';
        } else {
            $strProductType = '';

            if (($objProductType = Group::findByPk($row['id'])->getRelated('product_type')) !== null) {
                $strProductType = ' <span style="color:#b3b3b3; padding-left:3px;">[' . $objProductType->name . ']</span>';
            }

            return $image . ' ' . $label . $strProductType;
        }
    }

    /**
     * Reassign products to no group when group is deleted
     *
     * @param object $dc
     */
    public function deleteGroup($dc)
    {
        $arrGroups   = \Database::getInstance()->getChildRecords($dc->id, 'tl_iso_group');
        $arrGroups[] = $dc->id;

        \Database::getInstance()->query("UPDATE tl_iso_product SET gid=0 WHERE gid IN (" . implode(',', $arrGroups) . ")");
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
        if (!\BackendUser::getInstance()->isAdmin
            && (
                !is_array(\BackendUser::getInstance()->iso_groupp)
                || !in_array('create', \BackendUser::getInstance()->iso_groupp)
            )
        ) {
            return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        }

        return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
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
        if (!\BackendUser::getInstance()->isAdmin
            && (
                !is_array(\BackendUser::getInstance()->iso_groupp)
                || !in_array('delete', \BackendUser::getInstance()->iso_groupp)
            )
        ) {
            return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        }

        return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }
}
