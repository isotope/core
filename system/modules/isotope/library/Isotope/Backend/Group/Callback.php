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
use Contao\StringUtil;
use Contao\System;
use Isotope\Backend\Permission;
use Isotope\CompatibilityHelper;
use Isotope\Model\Group;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;


class Callback extends Backend
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

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

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
        $image = Image::getHtml(CompatibilityHelper::asset('images/folder-network.png'), '', $imageAttribute);

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
}
