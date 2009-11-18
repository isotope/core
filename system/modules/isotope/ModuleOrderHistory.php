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


class ModuleOrderHistory extends ModuleIsotopeBase
{

	protected $strTemplate = 'mod_orderhistory';
	
	
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ORDER HISTORY ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->store_ids = deserialize($this->store_ids);
		
		if (!FE_USER_LOGGED_IN || !is_array($this->store_ids) || !count($this->store_ids))
			return '';
		
		$this->import('FrontendUser', 'User');
		
		return parent::generate();
	}
	
	
	protected function compile()
	{
		$objOrders = $this->Database->prepare("SELECT *, (SELECT COUNT(*) FROM tl_iso_order_items WHERE pid=tl_iso_orders.id) AS items FROM tl_iso_orders WHERE pid=? AND store_id IN (" . implode(',', $this->store_ids) . ")")->execute($this->User->id);
		
		// No orders found, just display an "empty" message
		if (!$objOrders->numRows)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'];
			return;
		}
		
		$this->import('Isotope');
		$this->Isotope->overrideStore($this->store_id);
		
		$arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($this->jumpTo)->fetchAssoc();
		
		$arrOrders = array();
		while( $objOrders->next() )
		{
			$arrOrders[] = array
			(
				'raw'			=> $objOrders->row(),
				'date'			=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrders->date),
				'time'			=> $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objOrders->date),
				'datime'		=> $this->parseDate($GLOBALS['TL_CONFIG']['datimeFormat'], $objOrders->date),
				'items'			=> $objOrders->items,
				'grandTotal'	=> $this->Isotope->formatPriceWithCurrency($objOrders->grandTotal),
				'status'		=> $GLOBALS['TL_LANG']['MSC']['order_status_labels'][$objOrders->status],
				'link'			=> ($this->jumpTo ? $this->generateFrontendUrl($arrPage, '/uid/' . $objOrders->uniqid) : ''),
			);
		}
		
		$this->Template->orders = $arrOrders;
	}
}

