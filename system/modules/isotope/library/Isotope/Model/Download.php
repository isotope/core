<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\FilesModel;
use Contao\Model;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProduct;

/**
 * Download model represents a file or folder download for a product.
 */
class Download extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_download';


    /**
     * Gets array of files for this download (could be multiple for folder selection).
     *
     * @return array<FilesModel>
     */
    public function getFiles()
    {
        $objFile = $this->getRelated('singleSRC');

        if (null === $objFile) {
            return array();
        }

        if ('folder' === $objFile->type) {
            $arrFiles = array();
            $objFiles = FilesModel::findBy(array("pid=?", "type='file'"), array($objFile->id));

            if (null !== $objFiles) {
                while ($objFiles->next()) {
                    $arrFiles[] = $objFiles->current();
                }
            }

            return $arrFiles;
        }

        if (is_file(TL_ROOT . '/' . $objFile->path)) {
            return array($objFile);
        }

        return array();
    }

    /**
     * Calculates the expiration time of a download.
     *
     * @param int|null $intFrom
     *
     * @return int|null
     */
    public function getExpirationTimestamp($intFrom = null)
    {
        if ($this->expires == '') {
            return null;
        }

        $arrExpires = StringUtil::deserialize($this->expires, true);

        if (empty($arrExpires['value']) || empty($arrExpires['unit'])) {
            return null;
        }

        return strtotime('+' . $arrExpires['value'] . ' ' . $arrExpires['unit'], $intFrom);
    }

    /**
     * Finds downloads for a given product or variant.
     *
     *
     * @return Download[]|\Model\Collection|null
     */
    public static function findByProduct(IsotopeProduct $product)
    {
        $t = static::getTable();

        return static::findBy(
            array("($t.pid=? OR $t.pid=?)", "$t.published='1'"),
            array($product->getId(), $product->getProductId()),
            array('order' => 'sorting ASC')
        );
    }
}
