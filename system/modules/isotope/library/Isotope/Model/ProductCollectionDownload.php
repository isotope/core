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

namespace Isotope\Model;

use Isotope\Interfaces\IsotopeProductCollection;


/**
 * ProductCollectionDownload model represents a download in a collection (usually an order)
 *
 * @method \Isotope\Model\Download getRelated()
 *
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $download_id
 * @property string $downloads_remaining
 * @property string $expires
 */
class ProductCollectionDownload extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_download';


    /**
     * Check if downloads are remaining and is not expired
     * @return  bool
     */
    public function canDownload()
    {
        return (($this->downloads_remaining === '' || $this->downloads_remaining > 0) && ($this->expires == '' || $this->expires > time()));
    }

    /**
     * Send a file to browser and increase download counter
     * @param   string
     */
    protected function download($strFile)
    {
        if (TL_MODE == 'FE' && $this->downloads_remaining !== '') {
            \Database::getInstance()->prepare("UPDATE " . static::$strTable . " SET downloads_remaining=(downloads_remaining-1) WHERE id=?")->execute($this->id);
        }

        \Controller::sendFileToBrowser($strFile);
    }

    /**
     * Generate array representation for download
     * @return  array
     */
    public function getForTemplate($blnOrderPaid = false)
    {
        global $objPage;
        $objDownload = $this->getRelated('download_id');

        if (null === $objDownload) {
            return array();
        }

        $arrDownloads    = array();
        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

        foreach ($objDownload->getFiles() as $objFileModel) {

            $objFile = new \File($objFileModel->path, true);

            if (!in_array($objFile->extension, $allowedDownload) || preg_match('/^meta(_[a-z]{2})?\.txt$/', $objFile->basename)) {
                continue;
            }

            // Send file to the browser
            if (
                $blnOrderPaid &&
                $this->canDownload() &&
                \Input::get('download') == $objDownload->id &&
                \Input::get('file') == $objFileModel->path
            ) {
                $this->download($objFileModel->path);
            }


            $arrMeta = \Frontend::getMetaData($objFileModel->meta, $objPage->language);

            // Use the file name as title if none is given
            if ($arrMeta['title'] == '') {
                $arrMeta['title'] = specialchars(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename)));
            }

            $strHref = '';
            if (TL_MODE == 'FE') {
                $strHref = \Haste\Util\Url::addQueryString('download=' . $objDownload->id . '&amp;file=' . $objFileModel->path);
            }

            // Add the image
            $arrDownloads[] = array(
                'id'            => $this->id,
                'name'          => $objFile->basename,
                'title'         => $arrMeta['title'],
                'link'          => $arrMeta['title'],
                'caption'       => $arrMeta['caption'],
                'href'          => $strHref,
                'filesize'      => \System::getReadableSize($objFile->filesize, 1),
                'icon'          => TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon,
                'mime'          => $objFile->mime,
                'meta'          => $arrMeta,
                'extension'     => $objFile->extension,
                'path'          => $objFile->dirname,
                'remaining'     => ($objDownload->downloads_allowed > 0 ? sprintf($GLOBALS['TL_LANG']['MSC']['downloadsRemaining'], intval($this->downloads_remaining)) : ''),
                'downloadable'  => ($blnOrderPaid && $this->canDownload()),
            );
        }

        return $arrDownloads;
    }

    /**
     * Find all downloads that belong to items of a given collection
     * @param   IsotopeProductCollection
     * @param   array
     * @return  \Model\Collection|null
     */
    public static function findByCollection(IsotopeProductCollection $objCollection, array $arrOptions = array())
    {
        $arrOptions = array_merge(
			array(
				'column' => ("pid IN (SELECT id FROM tl_iso_product_collection_item WHERE pid=?)"),
				'value'  => $objCollection->id,
				'return' => 'Collection'
			),
			$arrOptions
		);

		return static::find($arrOptions);
    }

    /**
     * Create ProductCollectionDownload for all product downloads in the given collection
     * @param   IsotopeProductCollection
     * @return  array
     */
    public static function createForProductsInCollection(IsotopeProductCollection $objCollection)
    {
        $arrDownloads = array();
        $t            = Download::getTable();
        $time         = ($objCollection->locked ?: ($objCollection->tstamp ?: time()));

        foreach ($objCollection->getItems() as $objItem) {
            if ($objItem->hasProduct()) {
                $objDownloads = Download::findBy(array("($t.pid=? OR $t.pid=?)", "$t.published='1'"), array($objItem->getProduct()->id, $objItem->getProduct()->pid));

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
