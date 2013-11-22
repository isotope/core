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

use Isotope\Backend\SubtableVersion;
use Isotope\Model\Product;
use Isotope\Model\ProductCategory;


class Category extends \Backend
{

    /**
     * Update sorting of product in categories when duplicating, move new product to the bottom
     * @param integer
     * @param object
     * @link http://www.contao.org/callbacks.html#oncopy_callback
     */
    public function updateSorting($insertId, $dc)
    {
        $table = ProductCategory::getTable();

        $objCategories = \Database::getInstance()->query("SELECT c1.*, MAX(c2.sorting) AS max_sorting FROM $table c1 LEFT JOIN $table c2 ON c1.page_id=c2.page_id WHERE c1.pid=" . (int) $insertId . " GROUP BY c1.page_id");

        while ($objCategories->next())
        {
            \Database::getInstance()->query("UPDATE $table SET sorting=" . ($objCategories->max_sorting + 128) . " WHERE id=" . $objCategories->id);
        }
    }

    /**
     * Save categories history when creating new version of a product
     * @param   string
     * @param   int
     * @param   \DataContainer
     */
    public function createVersion($strTable, $intId, $dc)
    {
        if ($strTable != Product::getTable()) {
            return;
        }

        $objCategories = ProductCategory::findBy('pid', $intId);
        $arrCategories = (null === $objCategories ? array() : $objCategories->fetchAll());

        SubtableVersion::create($strTable, $intId, ProductCategory::getTable(), $arrCategories);
    }

    /**
     * Restore categories when restoring a product
     * @param   int
     * @param   string
     * @param   array
     * @param   int
     */
    public function restoreVersion($intId, $strTable, $arrData, $intVersion)
    {
        if ($strTable != Product::getTable()) {
            return;
        }

        $arrData = SubtableVersion::find(ProductCategory::getTable(), $intId, $intVersion);

        if (null !== $arrData) {
            \Database::getInstance()->query("DELETE FROM " . ProductCategory::getTable() . " WHERE pid=$intId");

            foreach ($arrData as $arrRow) {
                \Database::getInstance()->prepare("INSERT INTO " . ProductCategory::getTable() . " %s")->set($arrRow)->executeUncached();
            }
        }
    }

    /**
     * Load page IDs from product categories table
     * @param   mixed
     * @param   DataContainer
     * @return  mixed
     */
    public function load($varValue, \DataContainer $dc)
    {
        $objCategories = ProductCategory::findBy('pid', $dc->id);

        SubtableVersion::initialize($dc->table, $dc->id, ProductCategory::getTable(), (null === $objCategories ? array() : $objCategories->fetchAll()));

        return (null === $objCategories ? array() : $objCategories->fetchEach('page_id'));
    }

    /**
     * Save page ids to product category table. This allows to retrieve all products associated to a page.
     * @param   mixed
     * @param   DataContainer
     * @return  mixed
     */
    public function save($varValue, \DataContainer $dc)
    {
        $arrIds = deserialize($varValue);
        $table = ProductCategory::getTable();

        if (is_array($arrIds) && !empty($arrIds))
        {
            $time = time();

            if (\Database::getInstance()->query("DELETE FROM $table WHERE pid={$dc->id} AND page_id NOT IN (" . implode(',', $arrIds) . ")")->affectedRows > 0) {
                $dc->createNewVersion = true;
            }

            $objPages = \Database::getInstance()->execute("SELECT page_id FROM $table WHERE pid={$dc->id}");
            $arrIds = array_diff($arrIds, $objPages->fetchEach('page_id'));

            if (!empty($arrIds)) {
                foreach ($arrIds as $id) {
                    $sorting = (int) \Database::getInstance()->executeUncached("SELECT MAX(sorting) AS sorting FROM $table WHERE page_id=$id")->sorting + 128;
                    \Database::getInstance()->query("INSERT INTO $table (pid,tstamp,page_id,sorting) VALUES ({$dc->id}, $time, $id, $sorting)");
                }

                $dc->createNewVersion = true;
            }
        }
        else
        {
            if (\Database::getInstance()->query("DELETE FROM $table WHERE pid={$dc->id}")->affectedRows > 0) {
                $dc->createNewVersion = true;
            }
        }

        return '';
    }
}
