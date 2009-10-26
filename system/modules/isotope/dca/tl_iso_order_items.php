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
 * Table tl_iso_order_items
 */
$GLOBALS['TL_DCA']['tl_iso_order_items'] = array
(

  // Config
  'config' => array
  (
    'dataContainer'               => 'Table',
    'enableVersioning'            => false,
    'ptable'					  => 'tl_iso_orders'
  ),

  // List
  'list' => array
  (
    'sorting' => array
    (
      'mode'                    => 4,
      'fields'                  => array('tstamp DESC'),
      'flag'                    => 1,
      'headerFields'			=> array('tstamp','billing_address'),
      'panelLayout'             => 'filter',
      'child_record_callback'	=> array('tl_iso_order_items','generateRow')
    ),
    'label' => array
    (
      'fields'                  => array('grandTotal'),
      'label'                   => '%s',
      'label_callback'          => array('tl_iso_order_items', 'getOrderLabel')
    ),
    'operations' => array
    (
      'edit' => array
      (
        'label'               => &$GLOBALS['TL_LANG']['tl_iso_order_items']['edit'],
        'href'                => 'act=edit',
        'icon'                => 'edit.gif'
      ),
      'delete' => array
      (
        'label'               => &$GLOBALS['TL_LANG']['tl_iso_order_items']['delete'],
        'href'                => 'act=delete',
        'icon'                => 'delete.gif',
        'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
      ),
      'show' => array
      (
        'label'               => &$GLOBALS['TL_LANG']['tl_iso_order_items']['show'],
        'href'                => 'act=show',
        'icon'                => 'show.gif'
   	  )/*,
   	  'buttons' => array
	  (
		'button_callback'     => array('tl_iso_order_items', 'moduleOperations'),
	  )*/
    )
  ),

  // Palettes
  'palettes' => array
  (
    'default'                     => '{general_legend},product_name,price,product_options;{status_legend},status',
    
  ),

  // Fields
  'fields' => array
  (
		'product_name' => array
		(
			'input_field_callback'		=> array('tl_iso_order_items','displayValue')
			/*'label'                   => &$GLOBALS['TL_LANG']['tl_iso_order_items']['product_name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'locked'=>true, 'maxlength'=>255, 'tl_class'=>'w50')*/
		),
	  	'status' => array
	    (
	      'label'                   => &$GLOBALS['TL_LANG']['tl_iso_order_items']['status'],
	      'filter'                  => true,
	      'inputType'               => 'select',
	      'options'         		=> array('backordered','on_hold'),
	      'eval'					=> array('includeBlankOption'=>true),
	      'reference'         		=> &$GLOBALS['TL_LANG']['tl_iso_order_items'],
	    ),
  )
);


/** 
 *
 */
 
class tl_iso_order_items extends Backend
{
	public function __construct()
	{
		parent::__construct();
		
		$this->import('Isotope');	
	
	}
	
	public function displayValue($dc, $xlabel)
	{
	    $objProductName = $this->Database->prepare("SELECT product_name FROM tl_iso_order_items WHERE id=?")->limit(1)->execute($dc->id);


		return '<h1>' . $objProductName->product_name . '</h1>';
	}
	
	public function generateRow($arrRow)
	{
		$this->import('Isotope');
		
		
		
		//var_dump($arrRow);
		$strReturn = ($arrRow['status'] ? '<h2>' . $GLOBALS['TL_LANG']['tl_iso_order_items'][$arrRow['status']] . '</h2>' : '') . '<h1>' . $arrRow['product_name'] . ' - ' . $this->Isotope->formatPriceWithCurrency($arrRow['price']) . '</h1>' . (strlen($arrRow['product_options']) ? $this->getProductOptionsHTML(unserialize($arrRow['product_options'])) : '');
		
		return $strReturn;
	}
	
	
	public function getProductOptionsHTML($arrOptionData)
	{
		$strProductData = '';
		
		if(sizeof($arrOptionData))
        {
          	$strProductData .= '<h2>' . $GLOBALS['TL_LANG']['MSC']['productOptionsLabel'] . '</h2>';

        	foreach($arrOptionData as $rowData)
        	{       
        		$arrValues = deserialize($rowData['values']);
        				
		        $strProductData .= '<ul>';
		   		$strProductData .= '	<li>' . $rowData['name'] . ': ';
		        $strProductData .= implode(', ', $arrValues);
			    $strProductData .= '    </li>';     						
				$strProductData .= '</ul>'; 
        	}
        
        }
		
		return $strProductData;
		
	}
}

