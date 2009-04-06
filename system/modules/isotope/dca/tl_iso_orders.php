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
 * @package    Backend
 * @license    LGPL
 * @filesource
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
		'enableVersioning'            => true
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('id, tstamp'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;sort,search,limit'
		),
		'label' => array
		(
			'fields'                  => array(),
			'format'                  => '',
			'label_callback'          => array('tl_iso_orders', 'getOrderLabel')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			)/*,
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			)*/,
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
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
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => 'order_subtotal,order_tax,order_shipping_cost;shipping_method,status;billing_address_id,shipping_address_id;gift_message,gift_wrap;order_comments',
	),

	// Subpalettes
	'subpalettes' => array
	(
	
	),


	// Fields
	'fields' => array
	(
		
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
		)
	)
);


/**
 * Class tl_iso_orders
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Controller
 */
class tl_iso_orders extends Backend
{

	public function getOrderLabel($row, $label)
	{
		//get user name to tack onto order label
		setlocale(LC_MONETARY, $GLOBALS['TL_LANG']['MSC']['isotopeLocale'][$GLOBALS['TL_LANG']['MSC']['defaultCurrency']]);		

		$objUserName = $this->Database->prepare("SELECT firstname, lastname FROM tl_address_book WHERE id=?")
									  ->limit(1)
									  ->execute($row['billing_address_id']);
		if($objUserName->numRows < 1)
		{
			return '<no user name specified>';		
		}	
		
		$fltOrderTotal = (float)$row['order_subtotal'] + (float)$row['order_tax'] + (float)$row['order_shipping_cost'];
		
		$strBillingAddress = $this->loadAddress($row['billing_address_id'], $row['id']);
		$strShippingAddress = $this->loadAddress($row['shipping_address_id'], $row['id']);

		$strProductList = $this->getProducts($row['source_cart_id']);

		return '<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h110' : '') . ' block"><div><h2>Order #' . $row['id'] . '</h2>' . $objUserName->firstname . ' ' . $objUserName->lastname . '<br />Status: <strong>' . $GLOBALS['TL_LANG']['tl_iso_orders']['order_status_labels'][$row['status']] . '</strong><br />Shipping Method: ' . $GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method_labels'][$row['shipping_method']]  . '<br />Order Total: ' . money_format('%n', $fltOrderTotal) . '</div><br /><div style="display: inline;"><div style="width: 50%; float: left"><h2>Billing Address:</h2>' . $strBillingAddress . '</div><div style="width: 50%; float: left"><h2>Shipping Address:</h2>' . $strShippingAddress . '</div></div><div style="clear: both;"></div><h2>Cart Contents:</h2><div style="border: solid 1px #cccccc; margin: 10px; padding: 10px;">' . $strProductList . '</div><div style="clear: both;"></div><h2>Gift Wrap:</h2><div style="padding: 15px;">' . ($row['gift_wrap'] ? 'yes' : 'no') . '</div><div style="clear: both;"></div><h2>Gift Message:</h2><div style="padding: 15px;">' . $row['gift_message'] . '</div><div style="clear: both;"></div><h2>Order Comments:</h2><div style="padding: 15px;">' . $row['order_comments'] . '</div></div></div>';
	
	}
	
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


	protected function getPid($intId, $strTable)
	{
		if(!$this->Database->fieldExists('pid',$strTable))
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

?>