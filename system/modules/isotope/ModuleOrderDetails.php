<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleOrderDetails extends ModuleIsotopeBase
{

	protected $strTemplate = 'mod_orderdetails';
	
	
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ORDER DETAILS ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		return parent::generate();
	}
	
	
	protected function compile()
	{
		global $objPage;
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE uniqid=?")->limit(1)->execute($this->Input->get('uid'));
		
		if (!$objOrder->numRows)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'error';
			$this->Template->message = $GLOBALS['TL_LANG']['ERR']['orderNotFound'];
			return;
		}
		
		$this->Template->setData($objOrder->row());
		
		$this->import('Isotope');
		$this->Isotope->overrideStore($objOrder->store_id);
		
		// Article reader
		$arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($this->jumpTo)->fetchAssoc();
		
		$arrAllDownloads = array();
		$arrItems = array();
		$objItems = $this->Database->prepare("SELECT p.*, o.*, t.downloads AS downloads_allowed, (SELECT COUNT(*) FROM tl_iso_order_downloads d WHERE d.pid=o.id) AS has_downloads FROM tl_iso_order_items o LEFT OUTER JOIN tl_product_data p ON o.product_id=p.id LEFT OUTER JOIN tl_product_types t ON p.type=t.id WHERE o.pid=?")->execute($objOrder->id);
		
		
		while( $objItems->next() )
		{
			$objProduct = unserialize($objItems->product_data);
			
			if (!is_object($objProduct))
				continue;
			
			if ($objItems->downloads_allowed/* && $objItems->has_downlaods > 0*/)
			{
				$arrDownloads = array();
				$objDownloads = $this->Database->prepare("SELECT p.*, o.* FROM tl_iso_order_downloads o LEFT OUTER JOIN tl_product_downloads p ON o.download_id=p.id WHERE o.pid=?")->execute($objItems->id);
				
				while( $objDownloads->next() )
				{
					// Send file to the browser
					if (strlen($this->Input->get('file')) && $this->Input->get('file') == $objDownloads->id && ($objDownloads->downloads_allowed == 0 || $objDownloads->downloads_remaining > 0))
					{
						if ($objDownloads->downloads_remaining > 0)
						{
							$this->Database->prepare("UPDATE tl_iso_order_downloads SET downloads_remaining=? WHERE id=?")->execute(($objDownloads->downloads_remaining-1), $objDownloads->id);
						}
						
						$this->sendFileToBrowser($objDownloads->singleSRC);
					}
					
					$arrDownload = array
					(
						'raw'			=> $objDownloads->row(),
						'title'			=> $objDownloads->title,
						'href'			=> ($this->generateFrontendUrl($objPage->row()) . '?uid=' . $this->Input->get('uid') . '&amp;file=' . $objDownloads->id),
						'remaining'		=> ($objDownloads->downloads_allowed > 0 ? sprintf('<br />%s Downloads verbleibend', intval($objDownloads->downloads_remaining)) : ''),
						'downloadable'	=> (($objDownloads->downloads_allowed == 0 || $objDownloads->downloads_remaining > 0) ? true : false),
					);
					
					$arrDownloads[] = $arrDownload;
					$arrAllDownloads[] = $arrDownload;
				}
			}
			
			$arrItems[] = array
			(
				'raw'			=> $objItems->row(),
				'downloads'		=> (is_array($arrDownloads) ? $arrDownloads : array()),
				'name'			=> $objProduct->name,
				'quantity'		=> $objItems->quantity_sold,
				'price'			=> $this->Isotope->formatPriceWithCurrency($objProduct->price),
				'total'			=> $this->Isotope->formatPriceWithCurrency(($objProduct->price * $objItems->quantity_sold)),
				'href'			=> ($this->jumpTo ? $this->generateFrontendUrl($arrPage, '/product/'.$objItems->alias) : ''),
				'tax_id'		=> $objProduct->tax_id,
			);
		}
		
		
		$this->Template->info = deserialize($objOrder->checkout_info);
		$this->Template->items = $arrItems;
		$this->Template->downloads = $arrAllDownloads;
		$this->Template->downloadsLabel = $GLOBALS['TL_LANG']['MSC']['downloadsLabel'];
		
		$this->Template->raw = $objOrder->row();
		
		$this->Template->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrder->date);
		$this->Template->time = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objOrder->date);
		$this->Template->datim = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objOrder->date);
		$this->Template->datimLabel = $GLOBALS['TL_LANG']['MSC']['datimLabel'];
		
		$this->Template->subTotalPrice = $this->Isotope->formatPriceWithCurrency($objOrder->subTotal);
		$this->Template->grandTotal = $this->Isotope->formatPriceWithCurrency($objOrder->grandTotal);
		$this->Template->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		
		$arrSurcharges = array();
		foreach( deserialize($objOrder->surcharges) as $arrSurcharge )
		{
			$arrSurcharges[] = array
			(
				'label'			=> $arrSurcharge['label'],
				'price'			=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['price']),
				'total_price'	=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['total_price']),
				'tax_id'		=> $arrSurcharge['tax_id'],
			);
		}
		
		$this->Template->surcharges = $arrSurcharges;
		
		$this->Template->billing_label = 'Rechnungsadresse';
		$this->Template->billing_address = $this->Isotope->generateAddressString(deserialize($objOrder->billing_address));
		if (strlen($objOrder->shipping_method))
		{
			$arrShippingAddress = deserialize($objOrder->shipping_address);
			if (!is_array($arrShippingAddress) || $arrShippingAddress['id'] == -1)
			{
				$this->Template->has_shipping = false;
				$this->Template->billing_label = 'Rechnungs & Lieferaddresse';
			}
			else
			{
				$this->Template->has_shipping = true;
				$this->Template->shipping_label = 'Lieferadresse';
				$this->Template->shipping_address = $this->Isotope->generateAddressString($arrShippingAddress);
			}
		}
	}
}

