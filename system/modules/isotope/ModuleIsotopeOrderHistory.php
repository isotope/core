<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class ModuleIsotopeOrderHistory
 * Front end module Isotope "order history".
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
			$objTemplate = new BackendTemplate('be_wildcard');

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
		$objOrders = $this->Database->execute("SELECT id, (SELECT COUNT(*) FROM tl_iso_order_items WHERE pid=tl_iso_orders.id) AS items FROM tl_iso_orders WHERE status>0 AND pid=".$this->User->id." AND config_id IN (" . implode(',', $this->iso_config_ids) . ") ORDER BY date DESC");

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
    		$objOrder = new IsotopeOrder();
    		$objOrder->findBy('id', $objOrders->id);

			if ($this->Isotope->Config->id != $objOrder->config_id)
			{
				$this->Isotope->overrideConfig($objOrder->config_id);
			}

			$arrOrders[] = array
			(
			    'collection'    => $objOrder,
				'raw'			=> $objOrder->getData(),
				'date'			=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrder->date),
				'time'			=> $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objOrder->date),
				'datime'		=> $this->parseDate($GLOBALS['TL_CONFIG']['datimeFormat'], $objOrder->date),
				'items'			=> $objOrders->items,
				'grandTotal'	=> $this->Isotope->formatPriceWithCurrency($objOrder->grandTotal),
				'status'		=> $objOrder->statusLabel,
				'link'			=> ($this->jumpTo ? (IsotopeFrontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->jumpTo)) : ''),
				'class'         => $objOrder->statusAlias,
			);
		}

		$this->Template->orders = IsotopeFrontend::generateRowClass($arrOrders, '', 'class', 0, ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);
		$this->Template->dateLabel = $GLOBALS['TL_LANG']['MSC']['iso_order_date'];
		$this->Template->statusLabel = $GLOBALS['TL_LANG']['MSC']['iso_order_status'];
		$this->Template->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$this->Template->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$this->Template->quantityLabel = $GLOBALS['TL_LANG']['MSC']['iso_quantity_header'];
		$this->Template->detailsLabel = $GLOBALS['TL_LANG']['MSC']['detailLabel'];
	}
}

