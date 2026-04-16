<?php

namespace Isotope\CoreBundle\Model;

use Contao\FilesModel;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Product\ProductInterface;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $sorting
 * @property int    $tstamp
 * @property string $singleSRC
 * @property int    $downloads_allowed
 * @property string $expires
 * @property bool   $published
 */
class DownloadModel extends Model
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
            $objFiles = FilesModel::findBy(array("pid=?", "type='file'"), array($objFile->uuid));

            if (null !== $objFiles) {
                while ($objFiles->next()) {
                    $arrFiles[] = $objFiles->current();
                }
            }

            return $arrFiles;
        }

        if (is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objFile->path)) {
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
     * @return DownloadModel[]|\Contao\Model\Collection|null
     */
    public static function findByProduct(ProductInterface $product)
    {
        $t = static::getTable();

        return static::findBy(
            array("($t.pid=? OR $t.pid=?)", "$t.published='1'"),
            array($product->getId(), $product->getProductId()),
            array('order' => 'sorting ASC')
        );
    }
}
