<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_DCA']['tl_iso_orders']['list']['operations']['edit_items'] = array
(
	'label'				  => &$GLOBALS['TL_LANG']['tl_iso_orders']['edit_items'],
	'href'				  => 'key=edit_items',
	'icon'				  => 'system/modules/isotope_orderedit/html/edit_items.png'
); 

class tl_iso_order_edit extends Backend
{

	/** 
	 * Provides an interface to edit order items
	 *
	 * @access public
	 * @param object $dc
	 */
    public function editOrderItems($dc)
	{
		$objItems = $this->Database->prepare("SELECT id, pid, price, product_quantity, (SELECT name FROM tl_iso_products WHERE tl_iso_products.id=tl_iso_order_items.product_id) AS product_name FROM tl_iso_order_items WHERE pid=?")->execute($dc->id);
		
		$arrFields = array();
		$arrEditFields = array('price','product_quantity');
			
		$strBuffer .= '<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=edit_items', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['tl_iso_products']['quick_edit'][1], $dc->id).'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_iso_order_item_edit" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_iso_order_item_edit" />

<div class="tl_tbox block">
<table width="100%" border="0" cellpadding="5" cellspacing="0" summary="">
<thead>
<th align="center">' . $GLOBALS['TL_LANG']['tl_iso_products']['name'][0] . '</th>
<th align="center">'.$GLOBALS['TL_LANG']['tl_iso_orders']['price'][0].'</th>
<th>&nbsp;</th>
<th align="center">'.$GLOBALS['TL_LANG']['tl_iso_orders']['product_quantity'][0].'</th>
<th align="center">'.$GLOBALS['TL_LANG']['tl_iso_orders']['item_total'][0].'</th>';

//$strBuffer .= '<th><img src="system/themes/default/images/published.gif" width="16" height="16" alt="' . $GLOBALS['TL_LANG']['tl_iso_products']['published'][0].'" /></th></thead>';		
		
		$globalDoNotSubmit = false;
				
		while($objItems->next())
		{
						
			$arrWidgets = array();
			$doNotSubmit = false;
			$arrSet = array();
			$arrSet['id'] = $objItems->id;						
			foreach($arrEditFields as $field)
			{
				$arrWidgets[$field] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field], $field.'[' . $objItems->id .']', $objItems->{$field}));
			}
			/*
			$arrWidgets['sku'] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields']['sku'], 'sku[' . $objItems->id . ']', $objItems->sku));
			
			$arrWidgets['price'] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields']['price'], 'price[' . $objItems->id . ']', $objItems->price));
			
			$arrWidgets['weight'] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields']['weight'], 'weight[' . $objItems->id . ']', $objItems->weight));
			
			$arrWidgets['stock_quantity'] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields']['stock_quantity'], 'stock_quantity[' . $objItems->id . ']', $objItems->stock_quantity));
			*/

			foreach($arrWidgets as $key=>$objWidget)
			{
								
				switch($key)
				{
					case 'price':
					case 'product_quantity':
						$objWidget->class = 'tl_text_4';
						break;
					default:
						$objWidget->class = 'tl_text_3';
						break;
				}
			
				if ($this->Input->post('FORM_SUBMIT') == 'tl_iso_order_item_edit')
				{
					$objWidget->validate();
					
					if ($objWidget->hasErrors())
					{						
						$doNotSubmit = true;
						$globalDoNotSubmit = true;
					}
					else
					{												
						$arrSet[$key] = $objWidget->value;
					}
				}
			}
						
			if($this->Input->post('FORM_SUBMIT') == 'tl_iso_order_item_edit' && !$doNotSubmit)
			{	
				//update the values for each 
				$this->Database->prepare("UPDATE tl_iso_order_items %s WHERE id=?")
							   ->set($arrSet)
							   ->execute($arrSet['id']);
			}
			
			$strBuffer .= '
<tr>
	<td align="center">'.$objItems->product_name.'</td>';
	$i=0;
	foreach($arrEditFields as $field)
	{	
			
		$strBuffer .= ($i==1 ? '<td align="center">x</td><td align="center">'.$arrWidgets[$field]->generate().'</td><td align="center">'.$objItems->price*$objItems->{$field}.'</td>' : '<td align="center">' . $arrWidgets[$field]->generate().'</td>');
		$i++;
	}
	
	$strBuffer .= '</tr>';
		
		}	 // end $objItems->next()
		
		if ($this->Input->post('FORM_SUBMIT') == 'tl_iso_order_item_edit' && !$globalDoNotSubmit)
		{
			$objOrder = new IsotopeOrder();
						
			if ($objOrder->findBy('id', $dc->id))
			{				
				$objOrder->initializeOrder();	//Currently used to instantiate the payment & shipping objects
			
				$this->import('Isotope');
				
				$this->Isotope->Order = $objOrder;	//Todo - separate Cart from backend order.
			
				$arrSet = array
				(
					'subTotal'		=> $this->Isotope->Order->subTotal,
					'taxTotal'		=> $this->Isotope->Order->taxTotal,
					'shippingTotal' => $this->Isotope->Order->shippingTotal,
					'surcharges'	=> $this->Isotope->Order->getSurcharges(),
					'grandTotal'	=> $this->Isotope->Order->grandTotal
				);
			
				$this->Database->prepare("UPDATE tl_iso_orders %s WHERE id=?")
							   ->set($arrSet)
							   ->execute($dc->id);				
			}
				
			if (strlen($this->Input->post('saveNclose')))
			{
				$this->redirect(str_replace('&key=edit_items', '', $this->Environment->request));
			}
			else
			{
				$this->reload();
			}
		}
		
		return $strBuffer . '
</table>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'" />
  <input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'" />
</div>

</div>
</form>';
	}

}