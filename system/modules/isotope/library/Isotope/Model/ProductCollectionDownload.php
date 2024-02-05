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

use Contao\Controller;
use Contao\Database;
use Contao\File;
use Contao\Frontend;
use Contao\Input;
use Contao\Model;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Haste\Util\Url;
use Isotope\CompatibilityHelper;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection\Order;

/**
 * ProductCollectionDownload model represents a download in a collection (usually an order)
 *
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $download_id
 * @property string $downloads_remaining
 * @property string $expires
 */
class ProductCollectionDownload extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_download';


    /**
     * Check if downloads are remaining and is not expired
     *
     * @return bool
     */
    public function canDownload()
    {
        return (($this->downloads_remaining === '' || $this->downloads_remaining > 0)
            && ($this->expires == '' || $this->expires > time())
        );
    }

    /**
     * Send a file to browser and increase download counter
     *
     * @param string $strFile
     */
    protected function download($strFile)
    {
        if (CompatibilityHelper::isFrontend() && $this->downloads_remaining !== '') {
            Database::getInstance()->prepare("UPDATE " . static::$strTable . " SET downloads_remaining=(downloads_remaining-1) WHERE id=?")->execute($this->id);
        }

        Controller::sendFileToBrowser($strFile);
    }

    /**
     * Generate array representation for download
     *
     * @param bool     $blnOrderPaid
     * @param int|null $orderDetailsPageId
     *
     * @return array
     */
    public function getForTemplate($blnOrderPaid = false, $orderDetailsPageId = null)
    {
        /** @var Download $objDownload */
        $objDownload = $this->getRelated('download_id');

        if (null === $objDownload) {
            return array();
        }

        $arrDownloads    = array();
        $allowedDownload = StringUtil::trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

        $baseUrl = null;
        if ($orderDetailsPageId > 0 && ($orderDetailsPage = PageModel::findByPk($orderDetailsPageId)) !== null) {
            /** @var Order $order */
            $order = $this->getRelated('pid')->getRelated('pid');

            $baseUrl = $orderDetailsPage->getFrontendUrl().'?uid='.$order->uniqid;
        }

        foreach ($objDownload->getFiles() as $objFileModel) {
            $objFile = new File($objFileModel->path, true);

            if (!\in_array($objFile->extension, $allowedDownload)
                || preg_match('/^meta(_[a-z]{2})?\.txt$/', $objFile->basename)
            ) {
                continue;
            }

            // Send file to the browser
            if ($blnOrderPaid &&
                $this->canDownload() &&
                Input::get('download') == $objDownload->id &&
                Input::get('file') == $objFileModel->path
            ) {
                $path = $objFileModel->path;

                if (isset($GLOBALS['ISO_HOOKS']['downloadFromProductCollection'])
                    && \is_array($GLOBALS['ISO_HOOKS']['downloadFromProductCollection'])
                ) {
                    foreach ($GLOBALS['ISO_HOOKS']['downloadFromProductCollection'] as $callback) {
                        $path = System::importStatic($callback[0])->{$callback[1]}(
                            $path,
                            $objFileModel,
                            $objDownload,
                            $this
                        );
                    }
                }

                $this->download($objFileModel->path);
            }

            $arrMeta = Frontend::getMetaData($objFileModel->meta, $GLOBALS['TL_LANGUAGE']);

            // Use the file name as title if none is given
            if (empty($arrMeta['title'])) {
                $arrMeta['title'] = StringUtil::specialchars(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename)));
            }

            $strHref = '';
            if (CompatibilityHelper::isFrontend()) {
                $strHref = Url::addQueryString(
                    'download=' . $objDownload->id . '&amp;file=' . $objFileModel->path,
                    $baseUrl
                );
            }

            // Add the image
            $arrDownloads[] = array(
                'id'            => $this->id,
                'file'          => $objFile->path,
                'name'          => $objFile->basename,
                'title'         => $arrMeta['title'] ?? '',
                'link'          => $arrMeta['title'] ?? '',
                'caption'       => $arrMeta['caption'] ?? '',
                'href'          => $strHref,
                'filesize'      => System::getReadableSize($objFile->filesize, 1),
                'icon'          => TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon,
                'mime'          => $objFile->mime,
                'meta'          => $arrMeta,
                'extension'     => $objFile->extension,
                'path'          => $objFile->dirname,
                'remaining'     => $objDownload->downloads_allowed > 0 ? sprintf($GLOBALS['TL_LANG']['MSC']['downloadsRemaining'], (int) $this->downloads_remaining) : '',
                'downloadable'  => $blnOrderPaid && $this->canDownload(),
            );
        }

        return $arrDownloads;
    }

    /**
     * Find all downloads that belong to items of a given collection
     *
     * @param IsotopeProductCollection $objCollection
     * @param array                    $arrOptions
     *
     * @return Collection|null
     */
    public static function findByCollection(IsotopeProductCollection $objCollection, array $arrOptions = array())
    {
        $arrOptions = array_merge(
            array(
                'column' => 'pid IN (SELECT id FROM tl_iso_product_collection_item WHERE pid=?)',
                'value'  => $objCollection->getId(),
                'return' => 'Collection'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }

    /**
     * Create ProductCollectionDownload for all product downloads in the given collection
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return static[]
     */
    public static function createForProductsInCollection(IsotopeProductCollection $objCollection)
    {
        $arrDownloads = array();
        $time = $objCollection->isLocked() ? $objCollection->getLockTime(): time();

        foreach ($objCollection->getItems() as $objItem) {
            if ($objItem->hasProduct()) {
                $objDownloads = Download::findByProduct($objItem->getProduct());

                if (null !== $objDownloads) {
                    /** @var Download $objDownload */
                    foreach ($objDownloads as $objDownload) {
                        $objItemDownload              = new static();
                        $objItemDownload->pid         = $objItem->id;
                        $objItemDownload->tstamp      = $time;
                        $objItemDownload->download_id = $objDownload->id;

                        if ($objDownload->downloads_allowed > 0) {
                            $objItemDownload->downloads_remaining = ($objDownload->downloads_allowed * $objItem->quantity);
                        }

                        $expires = $objDownload->getExpirationTimestamp($time);
                        if (null !== $expires) {
                            $objItemDownload->expires = $expires;
                        }

                        $arrDownloads[] = $objItemDownload;
                    }
                }
            }
        }

        return $arrDownloads;
    }
}
