<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\Database;
use Contao\Input;
use Contao\Message;
use Contao\Session;
use Contao\System;
use Isotope\Model\Group;
use Isotope\Model\Product;

class Permission extends Backend
{

    /**
     * Check permissions for that entry
     * @return void
     */
    public static function check()
    {
        $session = Session::getInstance()->getData();

        if ('delete' === Input::get('act') && \in_array(Input::get('id'), static::getUndeletableIds())) {
            throw new InternalServerErrorException('Product ID '.Input::get('id').' is used in an order and can\'t be deleted');
        }

        if ('deleteAll' === Input::get('act') && \is_array($session['CURRENT']['IDS'])) {
            $arrDeletable = array_diff($session['CURRENT']['IDS'], static::getUndeletableIds());

            if (\count($arrDeletable) != \count($session['CURRENT']['IDS'])) {

                // Unpublish all undeletable records
                Database::getInstance()->query("
                    UPDATE " . Product::getTable() . "
                    SET published=''
                    WHERE id IN (" . implode(',', array_intersect($session['CURRENT']['IDS'], static::getUndeletableIds())) . ")
                ");

                // Remove undeletable products from selection
                $session['CURRENT']['IDS'] = array_values($arrDeletable);
                Session::getInstance()->setData($session);

                Message::addInfo($GLOBALS['TL_LANG']['MSC']['undeletableUnpublished']);
            }
        }

        $arrProducts = static::getAllowedIds();

        // Method will return true if no limits should be applied (e.g. user is admin)
        if (true === $arrProducts) {
            return;
        }

        // Filter by product type and group permissions
        if (empty($arrProducts)) {
            unset($session['CLIPBOARD']['tl_iso_product']);
            $session['CURRENT']['IDS']                                          = array();
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = array('id=?', 0);

            if (false === $arrProducts) {
                $GLOBALS['TL_DCA']['tl_iso_product']['config']['closed'] = true;
            }
        } else {
            // Maybe another function has already set allowed product IDs
            if (\is_array($GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'] ?? null)) {
                $arrProducts = array_intersect($GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'], $arrProducts);
            }

            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'] = $arrProducts;

            // Set allowed product IDs (edit multiple)
            if (\is_array($session['CURRENT']['IDS'] ?? null)) {
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root']);
            }

            // Set allowed clipboard IDs
            if (\is_array($session['CLIPBOARD']['tl_iso_product']['id'] ?? null)) {
                $session['CLIPBOARD']['tl_iso_product']['id'] = array_intersect($session['CLIPBOARD']['tl_iso_product']['id'], $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'], Database::getInstance()->query("SELECT id FROM tl_iso_product WHERE pid=0")->fetchEach('id'));

                if (empty($session['CLIPBOARD']['tl_iso_product']['id'])) {
                    unset($session['CLIPBOARD']['tl_iso_product']);
                }
            }

            // Overwrite session
            Session::getInstance()->setData($session);

            // Check if the product is accessible by user
            if (Input::get('id') > 0
                && !\in_array(Input::get('id'), $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'])
                && (!\is_array($session['new_records']['tl_iso_product'])
                    || !\in_array(Input::get('id'), $session['new_records']['tl_iso_product'])
                )
            ) {
                throw new AccessDeniedException('Cannot access product ID ' . Input::get('id'));
            }
        }
    }

    /**
     * Check if a product can be deleted by the current backend user
     * Deleting is prohibited if a product has been ordered
     *
     * @return array
     */
    public static function getUndeletableIds()
    {
        static $arrProducts;

        if (null === $arrProducts) {
            $arrProducts = Database::getInstance()->query("
                    SELECT i.product_id AS id FROM tl_iso_product_collection_item i
                    INNER JOIN tl_iso_product_collection c ON i.pid=c.id
                    WHERE c.type='order'
                UNION
                    SELECT p.pid AS id FROM tl_iso_product p
                    INNER JOIN tl_iso_product_collection_item i ON i.product_id=p.id
                    INNER JOIN tl_iso_product_collection c ON i.pid=c.id
                    WHERE p.pid>0 AND c.type='order'
            ")->fetchEach('id');
        }

        return $arrProducts;
    }

    /**
     * Returns an array of all allowed product IDs and variant IDs for the current backend user
     * @return array|bool
     */
    public static function getAllowedIds()
    {
        $objUser = BackendUser::getInstance();

        if ($objUser->isAdmin) {
            $arrProducts = true;
        } else {
            $arrNewRecords   = $_SESSION['BE_DATA']['new_records']['tl_iso_product'] ?? null;
            $arrProductTypes = $objUser->iso_product_types;
            $arrGroups       = array();

            // Return false if there are no product types
            if (!\is_array($arrProductTypes) || empty($arrProductTypes)) {
                return false;
            }

            // Find the user groups
            if (\is_array($objUser->iso_groups) && \count($objUser->iso_groups) > 0) {
                $arrGroups = array_merge($arrGroups, $objUser->iso_groups, Database::getInstance()->getChildRecords($objUser->iso_groups, Group::getTable()));

                if (\is_array($objUser->iso_groupp) && \in_array('rootPaste', $objUser->iso_groupp)) {
                    $arrGroups[] = 0;
                }
            }

            $objProducts = Database::getInstance()->execute("
                SELECT id FROM tl_iso_product
                WHERE pid=0
                    AND language=''
                    " . (empty($arrGroups) ? '' : 'AND gid IN (' . implode(',', $arrGroups) . ')') . "
                    AND (
                        type IN (" . implode(',', $arrProductTypes) . ')' .
                        ((\is_array($arrNewRecords) && !empty($arrNewRecords)) ? " OR id IN (".implode(',', $arrNewRecords).")" : '') .
                    ")
            ");

            if ($objProducts->numRows == 0) {
                return array();
            }

            $arrProducts = $objProducts->fetchEach('id');
            $arrProducts = array_merge(
                $arrProducts,
                Database::getInstance()->execute(
                    "SELECT id FROM tl_iso_product WHERE language='' AND pid IN(".implode(',', $arrProducts).")"
                )->fetchEach('id')
            );
        }

        // HOOK: allow extensions to define allowed products
        if (isset($GLOBALS['ISO_HOOKS']['getAllowedProductIds']) && \is_array($GLOBALS['ISO_HOOKS']['getAllowedProductIds'])) {
            foreach ($GLOBALS['ISO_HOOKS']['getAllowedProductIds'] as $callback) {
                $arrAllowed = System::importStatic($callback[0])->{$callback[1]}();

                if ($arrAllowed === false) {
                    return false;
                }

                if (\is_array($arrAllowed)) {
                    if ($arrProducts === true) {
                        $arrProducts = $arrAllowed;
                    } else {
                        $arrProducts = array_intersect($arrProducts, $arrAllowed);
                    }
                }
            }
        }

        $totalProducts = Database::getInstance()->execute("SELECT COUNT(*) AS count FROM tl_iso_product WHERE language=''")->count;

        // If all product are allowed, we don't need to filter
        if ($arrProducts === true || \count($arrProducts) == $totalProducts) {
            return true;
        }

        return $arrProducts;
    }
}
