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
    'closed'            => true,
    'onload_callback' => array
    (
      array('tl_iso_orders', 'hideReviewRecords'),
    ),
  ),

  // List
  'list' => array
  (
    'sorting' => array
    (
      'mode'                    => 2,
      'fields'                  => array('tstamp DESC'),
      'flag'                    => 1,
      'panelLayout'             => 'filter,search,limit'
    ),
    'label' => array
    (
      'fields'                  => array('grandTotal'),
      'label'                   => '%s',
      'label_callback'          => array('tl_iso_orders', 'getOrderLabel')
    ),
    'operations' => array
    (
      'edit' => array
      (
        'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['edit'],
        'href'                => 'act=edit',
        'icon'                => 'edit.gif'
      ),
/*
      'delete' => array
      (
        'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['delete'],
        'href'                => 'act=delete',
        'icon'                => 'delete.gif',
        'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
      ),
*/
      'show' => array
      (
        'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['show'],
        'href'                => 'act=show',
        'icon'                => 'show.gif'
      ),/*
      'authorize_process_payment' => array
      (
        'label'         => &$GLOBALS['TL_LANG']['tl_iso_orders']['authorize_process_payment'],
        'href'          => 'key=authorize_process_payment',
        'icon'          => 'system/modules/isotope/html/money.png'      
      ),
      'print_order' => array
      (
        'label'         => &$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'],
        'href'          => 'key=print_order',
        'icon'          => 'system/modules/isotope/html/printer.png'      
      )*/
    )
  ),

  // Palettes
  'palettes' => array
  (
    'default'                     => 'status,details',
  ),

  // Fields
  'fields' => array
  (
    'status' => array
    (
      'label'                   => &$GLOBALS['TL_LANG']['tl_iso_orders']['status'],
      'filter'                  => true,
      'inputType'               => 'select',
      'options'         => array('pending','processing','shipped','complete','on_hold', 'cancelled'),
      'reference'         => &$GLOBALS['TL_LANG']['MSC']['order_status_labels'],
    ),
    'details' => array
    (
      'input_field_callback'    => array('tl_iso_orders', 'showDetails'),
    ),/*
    'tstamp' => array
    (
      'label'                   => &$GLOBALS['TL_LANG']['tl_news']['tstamp'],
      'flag'                    => 8,
      'inputType'               => 'text',
      'eval'                    => array('rgxp'=>'date'),
    ),
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
    
    if (!$objOrders->numRows)
    {
      $GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = array('tl_iso_orders', 'injectPrintCSS');
      
      return $this->getOrderDescription($objOrder->row());
    }
      
    return '';
  }
  
  
  protected function getOrderDescription($row)
  {
    $strProductList = $this->getProducts($row['source_cart_id']);

    return '
		  <div>
		    <h2>' . $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] .': ' . $row['order_id'] . ' (#' . $row['id'] . ')</h2><!--
		    ' . 'von Gast-Benutzer' . '<br /> -->
		    ' . $GLOBALS['TL_LANG']['MSC']['iso_order_status'] . ': <strong>' . $GLOBALS['TL_LANG']['MSC']['order_status_labels'][$row['status']] . '</strong><br />
		    ' . $GLOBALS['TL_LANG']['MSC']['iso_payment_info_header'] . ': ' . $row['payment_method']  . '<br />
		    ' . $GLOBALS['TL_LANG']['MSC']['iso_shipping_info_header'] . ': ' . $row['shipping_method']  . '<br />
		    ' . $GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'] . ': ' . $row['subTotal'] . '<br />
		    ' . $GLOBALS['TL_LANG']['MSC']['iso_tax_header'] . ': ' . $row['taxTotal'] . '<br />
		    ' . $GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header'] . ': ' . $row['shippingTotal'] . '<br />
		    ' . $GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header'] . ': ' . $row['grandTotal'] . '
		  </div>
		  <br />
		  <div style="display: inline;">
		    <div style="width: 50%; float: left">
		      <h2>' . $GLOBALS['TL_LANG']['MSC']['iso_billing_address_header'] . ':</h2>
		      ' . nl2br($row['billing_address']) . '
		    </div>
		    <div style="width: 50%; float: left">
		      <h2>' . $GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header'] . ':</h2>
		      ' . nl2br($row['shipping_address']) . '
		    </div>
		  </div>
		  <div style="clear: both;"></div>
		  <h2>' . $GLOBALS['TL_LANG']['MSC']['iso_order_items'] . ':</h2>
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
          'table'       => $productData['storeTable'],
          'id'        => $productData['product_id'], 
          'quantity'      => $productData['quantity_requested']
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
                  
      $objProductExtendedData = $this->Database->prepare("SELECT id, name, price FROM " . $storeTable . " WHERE id IN(" . $strProductList . ")")
                           ->execute();
                  
      if($objProductExtendedData->numRows < 1)
      {
        continue;
      }   
      
      $arrProductExtendedData = $objProductExtendedData->fetchAllAssoc();
      
      foreach($arrProductExtendedData as $row)
      {
        
                
        $fltProductTotal = (int)$arrProductLists[$storeTable][$row['id']]['quantity'] * (float)$row['price']; 
      
        $fltProductPrice = (float)$row['price'];
      
      
        $strProductData .= $row['name'] . ' - ' . money_format('%n', $fltProductPrice) . ' x ' . $arrProductLists[$storeTable][$row['id']]['quantity'] . ' = ' . money_format('%n', $fltProductTotal) . '<br />';
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
  
  
  /**
   * Review order page stores temporary information in this table to know it when user is redirected to a payment provider. We do not show this data in backend.
   * 
   * @access public
   * @param object $dc
   * @return void
   */
  public function hideReviewRecords()
  {
    $objOrders = $this->Database->execute("SELECT * FROM tl_iso_orders WHERE status!=''");
    
    if (!$objOrders->numRows)
    {
      $GLOBALS['TL_DCA']['tl_iso_orders']['list']['sorting']['root'] = array(0);
    }
    else
    {
      $GLOBALS['TL_DCA']['tl_iso_orders']['list']['sorting']['root'] = $objOrders->fetchEach('id');
    }
  }
  
  
  public function injectPrintCSS($strBuffer)
  {
    return str_replace('</head>', '<link rel="stylesheet" type="text/css" href="system/modules/isotope/html/print.css" media="print" />' . "\n</head>", $strBuffer);
  }
}
?>