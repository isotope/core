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
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\Widget;
use Isotope\Backend\SubtableVersion;
use Isotope\Model\Product;
use Isotope\Model\ProductCategory;


class Category extends Backend
{

    /**
     * Update sorting of product in categories when duplicating, move new product to the bottom
     *
     * @param int $insertId
     *
     * @link http://www.contao.org/callbacks.html#oncopy_callback
     */
    public function updateSorting($insertId)
    {
        $objCategories = Database::getInstance()->prepare('
            SELECT id, page_id
            FROM tl_iso_product_category
            WHERE pid=?'
        )->execute($insertId);

        while ($objCategories->next()) {
            Database::getInstance()->prepare('
                UPDATE tl_iso_product_category
                SET sorting=(SELECT max_sorting FROM (SELECT MAX(sorting) AS max_sorting FROM tl_iso_product_category WHERE page_id=?) subq)+128
                WHERE id=?'
            )->execute($objCategories->page_id, $objCategories->id);
        }
    }

    /**
     * Save categories history when creating new version of a product
     * @param string $strTable
     * @param int    $intId
     */
    public function createVersion($strTable, $intId)
    {
        if ($strTable != Product::getTable()) {
            return;
        }

        $objCategories = ProductCategory::findBy('pid', $intId);
        $arrCategories = (null === $objCategories ? array() : $objCategories->fetchAll());

        SubtableVersion::create($strTable, $intId, ProductCategory::getTable(), $arrCategories);

        $current = Database::getInstance()
            ->prepare("SELECT * FROM tl_version WHERE fromTable=? AND pid=? AND active='1'")
            ->limit(1)
            ->execute($strTable, $intId)
        ;

        if (1 === $current->numRows) {
            $data = StringUtil::deserialize($current->data);
            $data['pages'] = array_map(function ($category) {
                return $category['id'];
            }, $arrCategories);

            Database::getInstance()
                ->prepare('UPDATE tl_version SET data=? WHERE id=?')
                ->execute(serialize($data), $current->id)
            ;
        }
    }

    /**
     * Restore categories when restoring a product
     *
     * @param int    $intId
     * @param string $strTable
     * @param array  $arrData
     * @param int    $intVersion
     */
    public function restoreVersion($intId, $strTable, $arrData, $intVersion)
    {
        if ($strTable != Product::getTable()) {
            return;
        }

        $arrData = SubtableVersion::find('tl_iso_product_category', $intId, $intVersion);

        if (null !== $arrData) {
            Database::getInstance()->query('DELETE FROM tl_iso_product_category WHERE pid=' . (int) $intId);

            $tableFields = array_flip(Database::getInstance()->getFieldNames('tl_iso_product_category'));

            Controller::loadDataContainer('tl_iso_product_category');

            foreach ($arrData as $data) {
                $data = array_intersect_key($data, $tableFields);

                // Reset fields added after storing the version to their default value (see contao/core#7755)
                foreach (array_diff_key($tableFields, $data) as $k=>$v) {
                    $data[$k] = Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA']['tl_iso_product_category']['fields'][$k]['sql']);
                }

                Database::getInstance()->prepare('INSERT INTO tl_iso_product_category %s')->set($data)->execute();
            }

            Database::getInstance()
                     ->prepare("UPDATE tl_version SET active='' WHERE pid=? AND fromTable=?")
                     ->execute($intId, 'tl_iso_product_category')
            ;

            Database::getInstance()
                     ->prepare('UPDATE tl_version SET active=1 WHERE pid=? AND fromTable=? AND version=?')
                     ->execute($intId, 'tl_iso_product_category', $intVersion)
            ;
        }
    }

    /**
     * Load page IDs from product categories table
     *
     * @param mixed          $varValue
     *
     * @return mixed
     */
    public function load($varValue, DataContainer $dc)
    {
        $objCategories = ProductCategory::findBy('pid', $dc->id);

        SubtableVersion::initialize($dc->table, $dc->id, ProductCategory::getTable(), (null === $objCategories ? array() : $objCategories->fetchAll()));

        return (null === $objCategories ? array() : $objCategories->fetchEach('page_id'));
    }

    /**
     * Save page ids to product category table. This allows to retrieve all products associated to a page.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function save($varValue, DataContainer $dc)
    {
        $db = Database::getInstance();
        $arrIds = StringUtil::deserialize($varValue);

        if (\is_array($arrIds) && !empty($arrIds)) {
            $time = time();

            if ($db->query("DELETE FROM tl_iso_product_category WHERE pid={$dc->id} AND page_id NOT IN (" . implode(',', $arrIds) . ')')->affectedRows > 0) {
                $dc->createNewVersion = true;
            }

            $objPages = $db->execute("SELECT page_id FROM tl_iso_product_category WHERE pid={$dc->id}");
            $arrIds   = array_diff($arrIds, $objPages->fetchEach('page_id'));

            if (!empty($arrIds)) {
                foreach ($arrIds as $id) {
                    $sorting = (int) $db->execute("
                        SELECT MAX(sorting) AS sorting
                        FROM tl_iso_product_category
                        WHERE page_id=$id
                    ")->sorting + 128;

                    $db->query("
                        INSERT INTO tl_iso_product_category (pid,tstamp,page_id,sorting)
                        VALUES ({$dc->id}, $time, $id, $sorting)
                    ");
                }

                $dc->createNewVersion = true;
            }
        } else {
            if ($db->query("DELETE FROM tl_iso_product_category WHERE pid={$dc->id}")->affectedRows > 0) {
                $dc->createNewVersion = true;
            }
        }

        return '';
    }
}
