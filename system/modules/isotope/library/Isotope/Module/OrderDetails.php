<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;


/**
 * Class OrderDetails
 *
 * Front end module Isotope "order details".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class OrderDetails extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_orderdetails';

    /**
     * Disable caching of the frontend page if this module is in use
     * @var boolean
     */
    protected $blnDisableCache = true;


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate($blnBackend=false)
    {
        if (TL_MODE == 'BE' && !$blnBackend) {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ORDER DETAILS ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if ($blnBackend) {
            $this->backend = true;
            $this->jumpTo = 0;
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        // Also check owner (see #126)
        if (($objOrder = Order::findOneBy('uniqid', (string) \Input::get('uid'))) === null || (FE_USER_LOGGED_IN === true && $objOrder->pid > 0 && \FrontendUser::getInstance()->id != $objOrder->pid)) {
            $this->Template = new \Isotope\Template('mod_message');
            $this->Template->type = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['orderNotFound'];

            return;
        }

        Isotope::overrideConfig($objOrder->config_id);

        $objTemplate = new \Isotope\Template($this->iso_collectionTpl);
        $objTemplate->linkProducts = true;

        \Isotope\Frontend::addCollectionToTemplate($objTemplate, $objOrder);

        $arrAllDownloads = array();
        $arrItems = $objTemplate->items;

        foreach ($arrItems as $k => $arrItem) {

            $objProduct = $arrItem['product'];
            $arrDownloads = $arrItem['hasProduct'] ? $this->getDownloadsForProduct($arrItem['product'], $objOrder->paid) : array();

            $arrItems[$k]['downloads'] = $arrDownload;

            $arrAllDownloads = array_merge($arrAllDownloads, $arrDownloads);
        }

        $objTemplate->items = $arrItems;

        $this->Template->collection = $objOrder;
        $this->Template->products = $objTemplate->parse();
        $this->Template->downloads = $arrAllDownloads;
        $this->Template->info = deserialize($objOrder->checkout_info, true);

        $this->Template->date = Isotope::formatDate($objOrder->date);
        $this->Template->time = Isotope::formatTime($objOrder->date);
        $this->Template->datim = Isotope::formatDatim($objOrder->date);
        $this->Template->orderDetailsHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline'], $objOrder->order_id, $this->Template->datim);
        $this->Template->orderStatus = sprintf($GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'], $objOrder->getStatusLabel());
        $this->Template->orderStatusKey = $objOrder->getStatusAlias();
    }

    /**
     * Generate downloads for a product
     * @param   IsotopeProduct
     * @param   bool
     * @return  array
     */
    protected function getDownloadsForProduct($objProduct, $blnOrderPaid=false)
    {
        $time = time();
        $arrDownloads = array();
        $objDownloads = $this->Database->prepare("SELECT p.*, c.* FROM tl_iso_product_collection_download c JOIN tl_iso_downloads p ON c.download_id=p.id WHERE c.pid=?")->execute($objProduct->collection_id);

        while ($objDownloads->next()) {
            $blnDownloadable = ($blnOrderPaid && ($objDownloads->downloads_remaining === '' || $objDownloads->downloads_remaining > 0) && ($objDownloads->expires == '' || $objDownloads->expires > $time)) ? true : false;

            if ($objDownloads->type == 'folder') {
                foreach (scan(TL_ROOT . '/' . $objDownloads->singleSRC) as $file) {
                    if (is_file(TL_ROOT . '/' . $objDownloads->singleSRC . '/' . $file)) {
                        $arrDownloads[] = $this->generateDownload($objDownloads->singleSRC . '/' . $file, $objDownloads, $blnDownloadable);
                    }
                }
            } else {
                $arrDownloads[] = $this->generateDownload($objDownloads->singleSRC, $objDownloads, $blnDownloadable);
            }
        }

        return $arrDownloads;
    }

    /**
     * Generate data array for a downloadable file
     * @param   string
     * @param   Database_Result
     * @param   bool
     * @return array
     */
    protected function generateDownload($strFile, $objDownload, $blnDownloadable)
    {
        $strUrl = '';
        $strFileName = basename($strFile);

        if (TL_MODE == 'FE') {
            global $objPage;

            $strUrl = \Isotope\Frontend::addQueryStringToUrl('download=' . $objDownload->id . ($objDownload->type == 'folder' ? '&amp;file='.$strFileName : ''));
        }

        $arrDownload = array(
            'raw'            => $objDownload->row(),
            'title'            => ($objDownload->type == 'folder' ? $strFileName : $objDownload->title),
            'href'            => $strUrl,
            'remaining'        => ($objDownload->downloads_allowed > 0 ? sprintf($GLOBALS['TL_LANG']['MSC']['downloadsRemaining'], intval($objDownload->downloads_remaining)) : ''),
            'downloadable'    => $blnDownloadable,
        );

        // Send file to the browser
        if ($blnDownloadable && \Input::get('download') != '' && \Input::get('download') == $objDownload->id && ($objDownload->type == 'file' || (\Input::get('file') != '' && \Input::get('file') == $strFileName))) {
            if (!$this->backend && $objDownload->downloads_remaining !== '') {
                $this->Database->prepare("UPDATE tl_iso_product_collection_download SET downloads_remaining=? WHERE id=?")->execute(($objDownload->downloads_remaining-1), $objDownload->id);
            }

            $this->sendFileToBrowser($strFile);
        }

        return $arrDownload;
    }
}
