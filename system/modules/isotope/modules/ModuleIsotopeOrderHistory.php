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

namespace Isotope;


/**
 * Class ModuleIsotopeOrderHistory
 *
 * Front end module Isotope "order history".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class ModuleIsotopeOrderHistory extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_orderhistory';

	/**
	 * Disable caching of the frontend page if this module is in use
	 * @var boolean
	 */
	protected $blnDisableCache = true;


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ORDER HISTORY ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$this->iso_config_ids = deserialize($this->iso_config_ids);

		if (FE_USER_LOGGED_IN !== true || !is_array($this->iso_config_ids) || !count($this->iso_config_ids)) // Can't use empty() because its an object property (using __get)
		{
			return '';
		}

		// Do not index or cache the page
		global $objPage;
		$objPage->noSearch = 1;
		$objPage->cache = 0;

		$this->import('FrontendUser', 'User');
		return parent::generate();
	}


	/**
	 * Generate the module
	 * @return void
	 */
	protected function compile()
	{
		$objOrders = $this->Database->execute("SELECT *, (SELECT COUNT(*) FROM tl_iso_order_items WHERE pid=tl_iso_orders.id) AS items, (SELECT name FROM tl_iso_orderstatus WHERE id=tl_iso_orders.status) AS statusLabel FROM tl_iso_orders WHERE status>0 AND pid=".$this->User->id." AND config_id IN (" . implode(',', $this->iso_config_ids) . ") ORDER BY date DESC");

		// No orders found, just display an "empty" message
		if (!$objOrders->numRows)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'];
			return;
		}

		$this->import('Isotope');
		$arrOrders = array();

		while ($objOrders->next())
		{
			if ($this->Isotope->Config->id != $objOrders->config_id)
			{
				$this->Isotope->overrideConfig($objOrders->config_id);
			}

			$arrOrders[] = array
			(
				'raw'			=> $objOrders->row(),
				'date'			=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrders->date),
				'time'			=> $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objOrders->date),
				'datime'		=> $this->parseDate($GLOBALS['TL_CONFIG']['datimeFormat'], $objOrders->date),
				'items'			=> $objOrders->items,
				'grandTotal'	=> $this->Isotope->formatPriceWithCurrency($objOrders->grandTotal),
				'status'		=> $objOrders->statusLabel,
				'link'			=> ($this->jumpTo ? (IsotopeFrontend::addQueryStringToUrl('uid=' . $objOrders->uniqid, $this->jumpTo)) : ''),
			);
		}

		$this->Template->orders = $arrOrders;
		$this->Template->dateLabel = $GLOBALS['TL_LANG']['MSC']['iso_order_date'];
		$this->Template->statusLabel = $GLOBALS['TL_LANG']['MSC']['iso_order_status'];
		$this->Template->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$this->Template->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$this->Template->quantityLabel = $GLOBALS['TL_LANG']['MSC']['iso_quantity_header'];
		$this->Template->detailsLabel = $GLOBALS['TL_LANG']['MSC']['detailLabel'];
	}
}

