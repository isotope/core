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
		'ctable'					  => array('tl_iso_order_items'),
		'closed'            		  => true,
		'onload_callback' 			  => array
		(
			array('tl_iso_orders', 'checkPermission'),
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('date DESC'),
			'flag'                    => 1,
			'panelLayout'             => 'filter,search,limit'
		),
		'label' => array
		(
			'fields'                  => array('grandTotal'),
			'label'                   => '%s',
			'label_callback'          => array('tl_iso_orders', 'getOrderLabel')
		),
		'global_operations' => array
		(
			'export_emails' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails'],
				'href'                => 'key=export_emails',
				'class'               => 'header_css_import',
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
			)/*,
			'edit_order' => array
			(
				'label'         => &$GLOBALS['TL_LANG']['tl_iso_orders']['edit_order'],
				'href'          => 'table=tl_iso_order_items',
				'icon'          => 'system/modules/isotope/html/edit_order.png'      
			)*/,
			'print_order' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'],
				'href'			=> 'key=print_order',
				'icon'			=> 'system/modules/isotope/html/printer.png'
			),
			'buttons' => array
			(
				'button_callback'     => array('tl_iso_orders', 'moduleOperations'),
			)
		)
	),
	
	// Palettes
	'palettes' => array
	(
		'default'                     => '{general_legend},status,shippingTotal;{details_legend},details',
	),
	
	// Fields
	'fields' => array
	(
		'status' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_iso_orders']['status'],
			'filter'                => true,
			'inputType'             => 'select',
			'options'         		=> array('pending','processing','shipped','complete','on_hold', 'cancelled'),
			'reference'         	=> &$GLOBALS['TL_LANG']['MSC']['order_status_labels'],
		),
		'shippingTotal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['shippingTotal'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255),
			'save_callback'			=> array
			(
				array('tl_iso_orders','saveShippingTotal')
			)
		),
		'details' => array
		(
			'input_field_callback'    => array('tl_iso_orders', 'showDetails'),
		),
		'date' => array
		(
			'flag'                    => 8,
			'eval'                    => array('rgxp'=>'date'),
		),
		/*,
		'shippingTotal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['shippingTotal'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255),
			'load_callback'			=> array
			(
				array('tl_iso_orders','loadShippingTotal')
			),
			'save_callback'			=> array
			(
				array('tl_iso_orders','saveShippingTotal')
			)
		),
		'subTotal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['subTotal'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
		'taxTotal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['taxTotal'],
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
			'options'         => array('ups_ground'),
			'eval'                    => array('includeBlankOption'=>true),
			'reference'         => &$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method_labels']
		),
		'order_comments' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['order_comments'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'textarea',
			'eval'            => array('isoEditable'=>true, 'isoCheckoutGroups'=>array('payment_method'))
		),
		'gift_message' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['gift_message'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'textarea',
			'eval'            => array('isoEditable'=>true, 'isoCheckoutGroups'=>array('payment_method'))
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

	public function __construct()
	{
		parent::__construct();
		
		$this->import('Isotope');
	}
	
	
	/**
	 * Return a string of more buttons for the orders module.
	 * 
	 * @todo Collect additional buttons from shipping modules.
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	public function moduleOperations($arrRow)
	{
		foreach($GLOBALS['ISO_ORDERS']['operations'] as $k=>$v)
		{
			$objPaymentMethod = $this->Database->prepare("SELECT checkout_info FROM tl_iso_orders WHERE id=?")
											   ->limit(1)
											   ->execute($arrRow['id']);
											   
			if(!$objPaymentMethod->numRows)
			{
				return '';
			}
			
			$arrCheckoutData = deserialize($objPaymentMethod->checkout_data);
			
			$objPaymentType = $this->Database->prepare("SELECT type FROM tl_payment_modules WHERE id=?")
											 ->limit(1)
											 ->execute($arrCheckoutData['payment_method_id']);
						
			
			if($objPaymentType->numRows && $objPaymentType['type']==$k)
			{				
					$strClass = $v;
	
					if (!strlen($strClass) || !$this->classFileExists($strClass))
						return '';
						
					try 
					{
						$objModule = new $strClass($arrRow);
						$strButtons .= $objModule->moduleOperations($arrRow['id']);
					}
					catch (Exception $e) {}

			}
			
			
						
			
		}
		
		return $strButtons;
	}
	
	
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
		return '
		<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h110' : '') . ' block">
		' . $this->getOrderDescription($row) . '
		</div>  </div>';
	}
	
	
	public function showDetails($dc, $xlabel)
	{
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")->limit(1)->execute($dc->id);
		
		if ($objOrder->numRows)
		{
			$GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = array('tl_iso_orders', 'injectPrintCSS');
			
			return $this->getOrderDescription($objOrder->row());
		}
	
		return '';
	}
	
	/** 
	* Adjust the grand total to reflect the new shipping total.
	*
	* @param variant $varValue
	* @param object $dc
	* @return $varValue
	*/
	public function saveShippingTotal($varValue, DataContainer $dc)
	{
		$objTotals = $this->Database->prepare("SELECT subTotal, taxTotal FROM tl_iso_orders WHERE id=?")->limit(1)->execute($dc->id);
		
		if($objTotals->numRows < 1)
		{
			//$this->log(sprintf('The order record %s was not found.  Check for database corruption.', $dc->id), TL_ERROR);
			return $varValue;
		}
		
		$fltGrandTotal = (float)$varValue + $objTotals->subTotal + $objTotals->taxTotal;
		
		$this->Database->prepare("UPDATE tl_iso_orders SET grandTotal=?")->execute($fltGrandTotal);
		
		return $varValue;
	}  
	
	
	protected function getOrderDescription($row)
	{
		$this->Input->setGet('uid', $row['uniqid']);
		$objModule = new ModuleOrderDetails($this->Database->execute("SELECT * FROM tl_module WHERE type='isoOrderDetails'"));
		return $objModule->generate(true);
	}
	
	
	/**
	* getProducts function.
	* 
	* @access protected
	* @param integer 
	* @return string
	*/
	protected function getProducts($intOrderId)
	{
		$objProducts = $this->Database->prepare("SELECT * FROM tl_iso_order_items WHERE pid=?")->execute($intOrderId);
		
		if (!$objProducts->numRows)
			return '';
	
		while( $objProducts->next() )
		{
			$objProduct = unserialize($objProducts->product_data);
			
			$fltProductTotal = (int)$objProducts->quantity_sold * (float)$objProducts->price;      
			
			$strProductData .= '<tr><td>' . $objProduct->name;
			
			$arrOptions = deserialize(deserialize($objProducts->product_options));
			
			if(is_array($arrOptions) && count($arrOptions))
			{
				$strProductData .= '<ul>';

				foreach($arrOptions as $rowData)
				{       
					$arrValues = deserialize($rowData['values']);
					
					$strProductData .= '	<li>' . $rowData['name'] . ': ';
					$strProductData .= implode(', ', $arrValues);
					$strProductData .= '    </li>';
				}
				
				$strProductData .= '</ul>';
			}
			
			$strProductData .= '</td>';
			
			$strProductData .= '<td>' . $objProducts->quantity_sold . '</td><td>' . $this->Isotope->formatPriceWithCurrency($objProducts->price) . '</td><td>' . $this->Isotope->formatPriceWithCurrency($fltProductTotal) . '</td>';
			
			$strProductData .= '</tr>';
			
		}
		
		return $strProductData;
	}
	

	/**
	* Review order page stores temporary information in this table to know it when user is redirected to a payment provider. We do not show this data in backend.
	* 
	* @access public
	* @param object $dc
	* @return void
	*/
	public function checkPermission()
	{
		$this->import('BackendUser', 'User');
		
		$arrStores = $this->User->iso_stores;
		
		if (!is_array($arrStores) || !count($arrStores))
			$arrStores = array(0);
		
		$objOrders = $this->Database->execute("SELECT * FROM tl_iso_orders WHERE status!=''" . ($this->User->isAdmin ? '' : " AND store_id IN (".implode(',', $arrStores).")"));
		
		$arrIds = $objOrders->fetchEach('id');
		
		if (!count($arrIds))
			$arrIds = array(0);
		
		$GLOBALS['TL_DCA']['tl_iso_orders']['list']['sorting']['root'] = $arrIds;
		
		if (!$this->User->isAdmin)
		{
			unset($GLOBALS['TL_DCA']['tl_iso_orderes']['list']['operations']['delete']);
			
			if ($this->Input->get('act') == 'delete' || (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrIds)))
				$this->redirect('typolight/main.php?act=error');
		}
	}


	public function injectPrintCSS($strBuffer)
	{
		return str_replace('</head>', '<link rel="stylesheet" type="text/css" href="system/modules/isotope/html/print.css" media="print" />' . "\n</head>", $strBuffer);
	}
}

