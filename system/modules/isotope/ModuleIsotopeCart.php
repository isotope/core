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


class ModuleIsotopeCart extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_cart';

		
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE CART ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{
		$arrProducts = $this->Isotope->Cart->getProducts();
		
		if (!count($arrProducts))
		{
		   $this->Template = new FrontendTemplate('mod_message');
		   $this->Template->type = 'empty';
		   $this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
		   return;
		}
		
		$objTemplate = new FrontendTemplate($this->iso_cart_layout);
		
		global $objPage;
		$strUrl = $this->generateFrontendUrl($objPage->row());
		
		$blnReload = false;
		$arrQuantity = $this->Input->post('quantity');
		$arrProductData = array();
		
		if (isset($GLOBALS['TL_HOOKS']['iso_getProductUpdates']) && is_array($GLOBALS['TL_HOOKS']['iso_getProductUpdates']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_getProductUpdates'] as $callback)
			{				
				$this->import($callback[0]);
				$arrProducts = $this->$callback[0]->$callback[1]($arrProducts, $this);
			}
		}
		
		foreach( $arrProducts as $i => $objProduct )
		{
			if ($this->Input->get('remove') == $objProduct->cart_id)
			{
				$this->Database->query("DELETE FROM tl_iso_cart_items WHERE id={$objProduct->cart_id}");
				$this->redirect((strlen($this->Input->get('referer')) ? base64_decode($this->Input->get('referer', true)) : $strUrl));
			}
			elseif ($this->Input->post('FORM_SUBMIT') == 'iso_cart_update' && is_array($arrQuantity) && $objProduct->cart_id)
			{
				$blnReload = true;
				if (!$arrQuantity[$objProduct->cart_id])
				{
					$this->Database->query("DELETE FROM tl_iso_cart_items WHERE id={$objProduct->cart_id}");
				}
				else
				{
					$this->Database->prepare("UPDATE tl_iso_cart_items SET product_quantity=? WHERE id={$objProduct->cart_id}")->executeUncached($arrQuantity[$objProduct->cart_id]);
				}
			}
			
			$arrProductData[] = array_merge($objProduct->getAttributes(), array
			(
				'id'				=> $objProduct->id,
				'image'				=> $objProduct->images->main_image,
				'link'				=> $objProduct->href_reader,
				'price'				=> $this->Isotope->formatPriceWithCurrency($objProduct->price),
				'total_price'		=> $this->Isotope->formatPriceWithCurrency($objProduct->total_price),
				'original_price'	=> $this->Isotope->formatPriceWithCurrency($objProduct->original_price),
				'tax_id'			=> $objProduct->tax_id,
				'quantity'			=> $objProduct->quantity_requested,
				'cart_item_id'		=> $objProduct->cart_id,
				'product_options'	=> $objProduct->getOptions(),
				'remove_link'		=> ampersand($strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?') . 'remove='.$objProduct->cart_id.'&referer='.base64_encode($this->Environment->request)),
				'remove_link_text'  => $GLOBALS['TL_LANG']['MSC']['removeProductLinkText'],
				'remove_link_title' => sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $objProduct->name),
				'class'				=> 'row_' . $i . ($i%2 ? ' even' : ' odd') . ($i==0 ? ' row_first' : ''),
			));
		}

		if ($blnReload)
		{
			$this->reload();
		}
		
		if (count($arrProductData))
		{
			$arrProductData[count($arrProductData)-1]['class'] .= ' row_last';
		}
		
		$arrSurcharges = array();
		foreach( $this->Isotope->Cart->getSurcharges() as $arrSurcharge )
		{
			$arrSurcharges[] = array
			(
			   'label'				=> $arrSurcharge['label'],
			   'price'				=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['price']),
			   'total_price'		=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['total_price']),
			   'tax_id'				=> $arrSurcharge['tax_id'],
			);
		}
				
		// HOOK for adding additional forms into the template
		if (isset($GLOBALS['TL_HOOKS']['iso_compileCart']) && is_array($GLOBALS['TL_HOOKS']['iso_compileCart']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_compileCart'] as $name => $callback)
			{
				$this->import($callback[0]);
				$strForm = $this->$callback[0]->$callback[1]($this, $objTemplate, $arrProductData, $arrSurcharges);
				
				if ($strForm !== false)
				{
				 	$arrForms[$name] = $strForm;
				}
			}
		}			
		
		$objTemplate->formId = 'iso_cart_update';
		$objTemplate->formSubmit = 'iso_cart_update';
		$objTemplate->action = $this->Environment->request;
		$objTemplate->products = $arrProductData;
		$objTemplate->cartJumpTo = $this->generateFrontendUrl($this->Database->execute("SELECT * FROM tl_page WHERE id={$this->iso_cart_jumpTo}")->fetchAssoc());
		$objTemplate->cartLabel = $GLOBALS['TL_LANG']['MSC']['cartBT'];
		$objTemplate->checkoutJumpToLabel = $GLOBALS['TL_LANG']['MSC']['checkoutBT'];
		$objTemplate->checkoutJumpTo = $this->generateFrontendUrl($this->Database->execute("SELECT * FROM tl_page WHERE id={$this->iso_checkout_jumpTo}")->fetchAssoc());
		
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->subTotal);
		$objTemplate->grandTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->grandTotal);
		$objTemplate->showOptions = false;	//!@todo make a module option.
		$objTemplate->surcharges = $arrSurcharges;
		$objTemplate->forms = $arrForms;

		$this->Template->cart = $objTemplate->parse();
	}
}

