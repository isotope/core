<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;


class Permission extends \Backend
{

    /**
     * Check permissions for that entry
     * @return void
     */
    public static function check()
    {
        $session     = \Session::getInstance()->getData();
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
            if (is_array($GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'])) {
                $arrProducts = array_intersect($GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'], $arrProducts);
            }

            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'] = $arrProducts;

            // Set allowed product IDs (edit multiple)
            if (is_array($session['CURRENT']['IDS'])) {
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root']);
            }

            // Set allowed clipboard IDs
            if (is_array($session['CLIPBOARD']['tl_iso_product']['id'])) {
                $session['CLIPBOARD']['tl_iso_product']['id'] = array_intersect($session['CLIPBOARD']['tl_iso_product']['id'], $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'], \Database::getInstance()->query("SELECT id FROM tl_iso_product WHERE pid=0")->fetchEach('id'));

                if (empty($session['CLIPBOARD']['tl_iso_product']['id'])) {
                    unset($session['CLIPBOARD']['tl_iso_product']);
                }
            }

            // Overwrite session
            \Session::getInstance()->setData($session);

            // Check if the product is accessible by user
            if (\Input::get('id') > 0 && !in_array(\Input::get('id'), $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root']) && !in_array(\Input::get('id'), $session['new_records']['tl_iso_product'])) {
                \System::log('Cannot access product ID ' . \Input::get('id'), __METHOD__, TL_ERROR);
                \Controller::redirect('contao/main.php?act=error');
            }
        }
    }

    /**
     * Returns an array of all allowed product IDs and variant IDs for the current backend user
     * @return array|bool
     */
    public static function getAllowedIds()
    {
        $objUser = \BackendUser::getInstance();

        if ($objUser->isAdmin) {
            $arrProducts = true;
        } else {
            $arrNewRecords   = $_SESSION['BE_DATA']['new_records']['tl_iso_product'];
            $arrProductTypes = $objUser->iso_product_types;
            $arrGroups       = array();

            // Return false if there are no product types
            if (!is_array($arrProductTypes) || empty($arrProductTypes)) {
                return false;
            }

            // Find the user groups
            if (is_array($objUser->iso_groups) && count($objUser->iso_groups) > 0) {
                $arrGroups = array_merge($arrGroups, $objUser->iso_groups, \Database::getInstance()->getChildRecords($objUser->iso_groups, Group::getTable()));
            }

            $objProducts = \Database::getInstance()->execute("
                SELECT id FROM tl_iso_product
                WHERE pid=0
                    AND language=''
                    " . (empty($arrGroups) ? '' : "AND gid IN (" . implode(',', $arrGroups) . ")") . "
                    AND (
                        type IN (" . implode(',', $arrProductTypes) . ")" .
                        ((is_array($arrNewRecords) && !empty($arrNewRecords)) ? " OR id IN (".implode(',', $arrNewRecords).")" : '') .
                    ")
            ");

            if ($objProducts->numRows == 0) {
                return array();
            }

            $arrProducts = $objProducts->fetchEach('id');
            $arrProducts = array_merge($arrProducts, \Database::getInstance()->getChildRecords($arrProducts, 'tl_iso_product'));
        }

        // HOOK: allow extensions to define allowed products
        if (isset($GLOBALS['ISO_HOOKS']['getAllowedProductIds']) && is_array($GLOBALS['ISO_HOOKS']['getAllowedProductIds'])) {
            foreach ($GLOBALS['ISO_HOOKS']['getAllowedProductIds'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrAllowed  = $objCallback->$callback[1]();

                if ($arrAllowed === false) {
                    return false;
                } elseif (is_array($arrAllowed)) {
                    if ($arrProducts === true) {
                        $arrProducts = $arrAllowed;
                    } else {
                        $arrProducts = array_intersect($arrProducts, $arrAllowed);
                    }
                }
            }
        }

        // If all product are allowed, we don't need to filter
        if ($arrProducts === true || count($arrProducts) == Product::countAll()) {
            return true;
        }

        return $arrProducts;
    }
}
