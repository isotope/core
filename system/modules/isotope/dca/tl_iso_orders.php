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
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_orders
 */
$GLOBALS['TL_DCA']['tl_iso_orders'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => false,
		'closed'					  => true,
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('tstamp DESC'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;sort,search,limit'
		),
		'label' => array
		(
			'fields'                  => array('grandTotal'),
			'label'	                  => '%s',
			'label_callback'          => array('tl_iso_orders', 'getOrderLabel')
		),
		'operations' => array
		(/*
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),*/
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),/*
			'authorize_process_payment'	=> array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_iso_orders']['authorize_process_payment'],
				'href'				  => 'key=authorize_process_payment',
				'icon'				  => 'system/modules/isotope/html/money.png'			
			),
			'print_order'	=> array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'],
				'href'				  => 'key=print_order',
				'icon'				  => 'system/modules/isotope/html/printer.png'			
			)*/
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => 'order_subtotal,order_tax,order_shipping_cost;shipping_method,status;billing_address_id,shipping_address_id;gift_message,gift_wrap;order_comments',
	),

	// Fields
	'fields' => array
	(
		'tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_news']['tstamp'],
			'flag'                    => 8,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'date'),
		),/*
		'order_subtotal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['order_subtotal'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
		'order_tax' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['order_tax'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
		'order_shipping_cost' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['order_shipping_cost'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
		'shipping_method' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'options'				  => array('ups_ground'),
			'eval'                    => array('includeBlankOption'=>true),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method_labels']
		),
		'status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['status'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'options'				  => array('pending','processing','shipped','complete','on_hold'),
			'eval'                    => array(),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_orders']['order_status_labels']
		),
		'order_comments' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['order_comments'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'textarea',
			'eval'					  => array('isoEditable'=>true, 'isoCheckoutGroups'=>array('payment_method'))
		),
		'gift_message' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['gift_message'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'textarea',
			'eval'					  => array('isoEditable'=>true, 'isoCheckoutGroups'=>array('payment_method'))
		),
		'gift_wrap' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['gift_wrap'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		)*/
	)
);


/**
 * tl_iso_orders class.
 * 
 * @extends Backend
 */
class tl_iso_orders extends Backend
{
	/**
	 * getOrderLabel function.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $label
	 * @return string
	 */
	public function getOrderLabel($row, $label)
	{
//		$strBillingAddress = $this->loadAddress($row['billing_address_id'], $row['id']);
//		$strShippingAddress = $this->loadAddress($row['shipping_address_id'], $row['id']);

		$strProductList = $this->getProducts($row['source_cart_id']);

		return '
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h110' : '') . ' block">
  <div>
    <h2>Bestellung #' . $row['id'] . '</h2><!--
    ' . 'von Gast-Benutzer' . '<br />
    Status: <strong>' . $GLOBALS['TL_LANG']['tl_iso_orders']['order_status_labels'][$row['status']] . '</strong><br />-->
    Zahlungsart: ' . $row['payment_method']  . '<br />
    Versandart: ' . $row['shipping_method']  . '<br />
    Subtotal: ' . $row['subTotal'] . '<br />
    Davon MwSt.: ' . $row['taxTotal'] . '<br />
    Versandkosten: ' . $row['shippingTotal'] . '<br />
    Total: ' . $row['grandTotal'] . '
  </div>
  <br />
  <div style="display: inline;">
    <div style="width: 50%; float: left">
      <h2>Rechnungsadresse:</h2>
      ' . nl2br($row['billing_address']) . '
    </div>
    <div style="width: 50%; float: left">
      <h2>Versandadresse:</h2>
      ' . nl2br($row['shipping_address']) . '
    </div>
  </div>
  <div style="clear: both;"></div>
  <h2>Artikel:</h2>
  <div style="border: solid 1px #cccccc; margin: 10px; padding: 10px;">
    ' . $strProductList . '
  </div>
  <div style="clear: both;"></div><!--
  <h2>Gift Wrap:</h2>
  <div style="padding: 15px;">
    ' . ($row['gift_wrap'] ? 'Ja' : 'Nein') . '
  </div>
  <div style="clear: both;"></div>
  <h2>Gift Message:</h2>
  <div style="padding: 15px;">
    ' . $row['gift_message'] . '
  </div>
  <div style="clear: both;"></div>
  <h2>Order Comments:</h2>
  <div style="padding: 15px;">
    ' . $row['order_comments'] . '
  </div>-->
  </div>
</div>';
	
	}
	
	
	/**
	 * getProducts function.
	 * 
	 * @access protected
	 * @param integer $intSourceCartId
	 * @return string
	 */
	protected function getProducts($intSourceCartId)
	{
		$arrProductListsByTable = array();
		$arrProductData = array();
		
		$objProductData = $this->Database->prepare("SELECT ci.product_id, ci.quantity_requested, p.storeTable FROM tl_cart_items ci, tl_product_attribute_sets p WHERE p.id = ci.attribute_set_id AND ci.pid =?")
										 ->execute($intSourceCartId);
		
		if($objProductData->numRows < 1)
		{
			return '';
		}
		
		$arrProductData = $objProductData->fetchAllAssoc();
		
		
		foreach($arrProductData as $productData)
		{
			$arrProductLists[$productData['storeTable']][$productData['product_id']] = array
			(
					'table'				=> $productData['storeTable'],
					'id'				=> $productData['product_id'], 
					'quantity'			=> $productData['quantity_requested']
			);
		}

			
		foreach(array_keys($arrProductLists) as $storeTable)
		{					
			
			$fltProductTotal = 0.00;
			
			//Build list of product ids to work with
			foreach($arrProductLists[$storeTable] as $row)
			{
				$arrProductIds[] = $row['id'];
			}
			
			$strProductList = implode(',', $arrProductIds);
									
			$objProductExtendedData = $this->Database->prepare("SELECT id, product_name, product_price FROM " . $storeTable . " WHERE id IN(" . $strProductList . ")")
													 ->execute();
									
			if($objProductExtendedData->numRows < 1)
			{
				continue;
			}		
			
			$arrProductExtendedData = $objProductExtendedData->fetchAllAssoc();
			
			foreach($arrProductExtendedData as $row)
			{
				
								
				$fltProductTotal = (int)$arrProductLists[$storeTable][$row['id']]['quantity'] * (float)$row['product_price'];	
			
				$fltProductPrice = (float)$row['product_price'];
			
			
				$strProductData .= $row['product_name'] . ' - ' . money_format('%n', $fltProductPrice) . ' x ' . $arrProductLists[$storeTable][$row['id']]['quantity'] . ' = ' . money_format('%n', $fltProductTotal) . '<br />';
			}	
		}
		
		return $strProductData;
	}
	
	
	/**
	 * loadAddress function.
	 * 
	 * @todo Return value "no address specified" must be possible to translate
	 *
	 * @access protected
	 * @param mixed $varValue
	 * @param integer $intId
	 * @return string
	 */
	protected function loadAddress($varValue, $intId)
	{
		$intPid = $this->getPid($intId, 'tl_iso_orders');
	
		$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=? and pid=?")
									 ->limit(1)
									 ->execute($varValue, $intPid);
		
		if($objAddress->numRows < 1)
		{
			return 'no address specified';
		}
		
		$strAddress = $objAddress->firstname . ' ' . $objAddress->lastname . "<br />";
		$strAddress .= $objAddress->street . "<br />";
		$strAddress .= $objAddress->city . ', ' . $objAddress->state . '  ' . $objAddress->postal . "<br />";
		$strAddress .= $objAddress->country;

		return $strAddress;
	}


	/**
	 * getPid function.
	 * 
	 * @access protected
	 * @param integer $intId
	 * @param string $strTable
	 * @return integer
	 */
	protected function getPid($intId, $strTable)
	{
		if(!$this->Database->fieldExists('pid', $strTable))
		{
			return 0;
		}
		
		$objPid = $this->Database->prepare("SELECT pid FROM " . $strTable . " WHERE id=?")
								 ->limit(1)
								 ->execute($intId);
		
		if($objPid->numRows < 1)
		{
			return 0;
		}
		
		return $objPid->pid;
	}
}
