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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleShoppingCart extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_cart_full';
	
	/**
	 * Recall product data, if db has been updated with new information.
	 * @param boolean
	 */
	protected $blnRecallProductData = false;

	
	/** 
	 * 
	 * @param boolean
	 */
	
	protected $sessCartId;
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE SHOPPING CART ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Fallback template
		if (strlen($this->iso_cart_layout))
		{
			$this->strTemplate = $this->iso_cart_layout;
		}

		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{
		$arrProducts = $this->Cart->getProducts();
		
		if (!count($arrProducts))
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
			return;
		}
		
		global $objPage;
		$blnReload = false;
		$arrQuantity = $this->Input->post('quantity');
		$arrProductData = array();
		
		foreach( $arrProducts as $objProduct )
		{
			if ($this->Input->get('action') == 'remove' && $this->Input->get('id') == $objProduct->cart_id)
			{
				$this->Database->prepare("DELETE FROM tl_cart_items WHERE id=?")->execute($objProduct->cart_id);
				$this->redirect($this->generateFrontendUrl($objPage->row()));
			}
			elseif ($this->Input->post('FORM_SUBMIT') == 'iso_cart_update' && is_array($arrQuantity) && $objProduct->cart_id)
			{
				$blnReload = true;
				if (!$arrQuantity[$objProduct->cart_id])
				{
					$this->Database->prepare("DELETE FROM tl_cart_items WHERE id=?")->execute($objProduct->cart_id);
				}
				else
				{
					$this->Database->prepare("UPDATE tl_cart_items SET quantity_requested=? WHERE id=?")->execute($arrQuantity[$objProduct->cart_id], $objProduct->cart_id);
				}
			}
			
			$arrProductData[] = array
			(
				'id'				=> $objProduct->id,
				'image'				=> $objProduct->images[0],
				'name'				=> $objProduct->name,
				'link'				=> $objProduct->href_reader,
				'price'				=> $this->generatePrice($objProduct->price, $this->strPriceTemplate),
				'total_price'		=> $this->generatePrice($objProduct->total_price),
				'quantity'			=> $objProduct->quantity_requested,
				'cart_item_id'		=> $objProduct->cart_id,
				'remove_link'		=> $this->generateFrontendUrl($objPage->row(), '/action/remove/id/'.$objProduct->cart_id),
				'remove_link_title' => sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $objProduct->name)
			);
		}
		
		if ($blnReload)
		{
			$this->reload();
		}
		
		
		$this->Template->products = $arrProductData;
		$this->Template->cartJumpTo = $this->generateFrontendUrl($this->Database->prepare("SELECT id,alias FROM tl_page WHERE id=?")->execute($this->Isotope->Store->cartJumpTo)->fetchAssoc());
		$this->Template->checkoutJumpTo = $this->generateFrontendUrl($this->Database->prepare("SELECT id,alias FROM tl_page WHERE id=?")->execute($this->Isotope->Store->checkoutJumpTo)->fetchAssoc());
		$this->Template->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$this->Template->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$this->Template->subTotalPrice = $this->generatePrice($this->Cart->subTotal, 'stpl_total_price');
		$this->Template->grandTotalPrice = $this->generatePrice($this->Cart->subTotal, 'stpl_total_price');		// FIXME
	}
}

