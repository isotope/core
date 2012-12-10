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

use Isotope\Product\Collection\Order;


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
		if (TL_MODE == 'BE' && !$blnBackend)
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ORDER DETAILS ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		if ($blnBackend)
		{
			$this->backend = true;
			$this->jumpTo = 0;
		}

		return parent::generate();
	}


	/**
	 * Generate the module
	 * @return void
	 */
	protected function compile()
	{
		// Do not index or cache the page
		if (TL_MODE == 'FE')
		{
			global $objPage;
			$objPage->noSearch = 1;
			$objPage->cache = 0;
		}

		if (($objOrder = Order::findOneByUniqid(\Input::get('uid'))) === null)
		{
			$this->Template = new \FrontendTemplate('mod_message');
			$this->Template->type = 'error';
			$this->Template->message = $GLOBALS['TL_LANG']['ERR']['orderNotFound'];
			return;
		}

		$arrOrder = $objOrder->getData();
		$this->Template->setData($arrOrder);

		$this->import('Isotope\Isotope', 'Isotope');
		$this->Isotope->overrideConfig($objOrder->config_id);

		// Article reader
		$arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($this->jumpTo)->fetchAssoc();

		$arrAllDownloads = array();
		$arrItems = array();
		$arrProducts = $objOrder->getProducts();

		foreach ($arrProducts as $i => $objProduct)
		{
			$arrDownloads = $this->getDownloadsForProduct($objProduct, $objOrder->paid);

			$arrItems[] = array
			(
				'raw'				=> $objProduct->getData(),
				'sku'				=> $objProduct->sku,
				'name'				=> $objProduct->name,
				'image'				=> $objProduct->images->main_image,
				'product_options'	=> $objProduct->getOptions(),
				'quantity'			=> $objProduct->quantity_requested,
				'price'				=> $this->Isotope->formatPriceWithCurrency($objProduct->price),
				'tax_free_price'	=> $this->Isotope->formatPriceWithCurrency($objProduct->tax_free_price),
				'total'				=> $this->Isotope->formatPriceWithCurrency($objProduct->total_price),
				'tax_free_total'	=> $this->Isotope->formatPriceWithCurrency($objProduct->tax_free_total_price),
				'href'				=> ($this->jumpTo ? $this->generateFrontendUrl($arrPage, ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/product/') . $objProduct->alias) : ''),
				'tax_id'			=> $objProduct->tax_id,
				'downloads'			=> $arrDownloads,
			);

			$arrAllDownloads = array_merge($arrAllDownloads, $arrDownloads);
		}

		$this->Template->info = deserialize($objOrder->checkout_info, true);
		$this->Template->collection = $objOrder;
		$this->Template->items = \Isotope\Frontend::generateRowClass($arrItems, 'row', 'rowClass', 0, ISO_CLASS_COUNT|ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);
		$this->Template->downloads = $arrAllDownloads;
		$this->Template->downloadsLabel = $GLOBALS['TL_LANG']['MSC']['downloadsLabel'];

		$this->Template->raw = $arrOrder;

		$this->Template->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrder->date);
		$this->Template->time = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objOrder->date);
		$this->Template->datim = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objOrder->date);
		$this->Template->orderDetailsHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline'], $objOrder->order_id, $this->Template->datim);
		$this->Template->orderStatus = sprintf($GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'], $objOrder->statusLabel);
		$this->Template->orderStatusKey = standardize($objOrder->statusLabel);
		$this->Template->subTotalPrice = $this->Isotope->formatPriceWithCurrency($objOrder->subTotal);
		$this->Template->grandTotal = $this->Isotope->formatPriceWithCurrency($objOrder->grandTotal);
		$this->Template->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$this->Template->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$this->Template->surcharges = \Isotope\Frontend::formatSurcharges($objOrder->getSurcharges());
		$this->Template->billing_label = $GLOBALS['TL_LANG']['ISO']['billing_address'];
		$this->Template->billing_address = $objOrder->billingAddress->generateHtml($this->Isotope->Config->billing_fields);

		if (strlen($objOrder->shipping_method))
		{
			$arrShippingAddress = $objOrder->shipping_address;

			if (!is_array($arrShippingAddress) || $arrShippingAddress['id'] == -1)
			{
				$this->Template->has_shipping = false;
				$this->Template->billing_label = $GLOBALS['TL_LANG']['ISO']['billing_shipping_address'];
			}
			else
			{
				$this->Template->has_shipping = true;
				$this->Template->shipping_label = $GLOBALS['TL_LANG']['ISO']['shipping_address'];
				$this->Template->shipping_address = $objOrder->shippingAddress->generateHtml($this->Isotope->Config->shipping_fields);
			}
		}
	}


	protected function getDownloadsForProduct($objProduct, $blnOrderPaid=false)
	{
		$time = time();
		$arrDownloads = array();
		$objDownloads = $this->Database->prepare("SELECT p.*, o.* FROM tl_iso_order_downloads o JOIN tl_iso_downloads p ON o.download_id=p.id WHERE o.pid=?")->execute($objProduct->cart_id);

		while ($objDownloads->next())
		{
			$blnDownloadable = ($blnOrderPaid && ($objDownloads->downloads_remaining === '' || $objDownloads->downloads_remaining > 0) && ($objDownloads->expires == '' || $objDownloads->expires > $time)) ? true : false;

			if ($objDownloads->type == 'folder')
			{
				foreach (scan(TL_ROOT . '/' . $objDownloads->singleSRC) as $file)
				{
					if (is_file(TL_ROOT . '/' . $objDownloads->singleSRC . '/' . $file))
					{
						$arrDownloads[] = $this->generateDownload($objDownloads->singleSRC . '/' . $file, $objDownloads, $blnDownloadable);
					}
				}
			}
			else
			{
				$arrDownloads[] = $this->generateDownload($objDownloads->singleSRC, $objDownloads, $blnDownloadable);
			}
		}

		return $arrDownloads;
	}


	protected function generateDownload($strFile, $objDownload, $blnDownloadable)
	{
		$strUrl = '';
		$strFileName = basename($strFile);

		if (TL_MODE == 'FE')
		{
			global $objPage;

			$strUrl = \Isotope\Frontend::addQueryStringToUrl('download=' . $objDownload->id . ($objDownload->type == 'folder' ? '&amp;file='.$strFileName : ''));
		}

		$arrDownload = array
		(
			'raw'			=> $objDownload->row(),
			'title'			=> ($objDownload->type == 'folder' ? $strFileName : $objDownload->title),
			'href'			=> $strUrl,
			'remaining'		=> ($objDownload->downloads_allowed > 0 ? sprintf($GLOBALS['TL_LANG']['MSC']['downloadsRemaining'], intval($objDownload->downloads_remaining)) : ''),
			'downloadable'	=> $blnDownloadable,
		);

		// Send file to the browser
		if ($blnDownloadable && \Input::get('download') != '' && \Input::get('download') == $objDownload->id && ($objDownload->type == 'file' || (\Input::get('file') != '' && \Input::get('file') == $strFileName)))
		{
			if (!$this->backend && $objDownload->downloads_remaining !== '')
			{
				$this->Database->prepare("UPDATE tl_iso_order_downloads SET downloads_remaining=? WHERE id=?")->execute(($objDownload->downloads_remaining-1), $objDownload->id);
			}

			$this->sendFileToBrowser($strFile);
		}

		return $arrDownload;
	}
}

