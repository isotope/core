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


class ModuleGiftRegistryManager extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_registry';
	
	/**
	 * Template
	 * @var string
	 */
	protected $strFormId = 'iso_registry_create';
	
	/**
	 * for widgets, don't submit if certain validation(s) fail
	 * @var boolean;
	 */
	protected $doNotSubmit = false;
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE GIFT REGISTRY MANAGER ###';
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
		$this->import('IsotopeRegistryFrontend');
		
		global $objPage;
		$strUrl = $this->generateFrontendUrl($objPage->row());
	
		//Either load the existing Isotope->Registry object or set it if a registry exists, otherwise allow the user to create one
		if(!$this->IsotopeRegistryFrontend->registryExists() || $this->Input->get('action')=='edit')
		{

			$objTemplate = new FrontendTemplate('iso_registry_create');
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noItemsInRegistry'];
			$arrWidgets = $this->getWidgets();
			
			foreach($arrWidgets as $objWidget)
			{
				$objWidget->storeValues = true;
				$objWidget->tableless = true;	
				$objTemplate->fields .= '<span class="widget '. $objWidget->class .'">';
		
				if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
				{
					$objWidget->validate();
					$varValue = $objWidget->value;
						
				
					// Do not submit if there are errors
					if ($objWidget->hasErrors())
					{				
						$this->doNotSubmit = true;
					}
				}
				
				$objTemplate->fields .= $objWidget->parse() . '</span><br />';
			}
			
			if($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit)
			{
				$arrData = array
				(
					'pid'					=>	($this->User->id ? $this->User->id : 0),
					'tstamp'				=>	time(),
					'name'					=>	$this->Input->post('name'),
					'second_party_name'		=>	$this->Input->post('second_party_name'),
					'date'					=>	strtotime($this->Input->post('date')),
					'event_type'			=>	$this->Input->post('event_type'),
					'description'			=>	htmlentities($this->Input->post('description')),
				);
				
				$this->import('Isotope');
				
				if(!$this->Isotope->Registry)
				{
					$this->Isotope->Registry = new IsotopeRegistry();
					$this->Isotope->Registry->initializeRegistry($arrData);
				} else
				{
					$this->Isotope->Registry->setData($arrData);
					$this->Isotope->Registry->save();
				}
				
				$this->redirect($strUrl);
				
			}
			
			$objTemplate->action = ampersand($this->Environment->request, true);
			$objTemplate->formId = $this->strFormId;
			$objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['registryManage']);
			
			$this->Template->registry = $objTemplate->parse();
			return;
		
			
		}else
		{
			$this->Isotope->Registry->updateSold();
			$arrProducts = $this->Isotope->Registry->getProducts();
			
			if (!count($arrProducts))
			{
			   $this->Template = new FrontendTemplate('mod_message');
			   $this->Template->type = 'empty';
			   $this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInRegistry'];
			   return;
			}
			
			$objTemplate = new FrontendTemplate($this->iso_registry_layout);
			
			$blnReload = false;
			$arrQuantity = $this->Input->post('quantity');
			$arrProductData = array();
			
			foreach( $arrProducts as $i => $objProduct )
			{
				if ($this->Input->get('remove') == $objProduct->cart_id)
				{
					$this->Database->query("DELETE FROM tl_iso_registry_items WHERE id={$objProduct->cart_id}");
					$this->redirect((strlen($this->Input->get('referer')) ? base64_decode($this->Input->get('referer', true)) : $strUrl));
				}
				elseif ($this->Input->post('FORM_SUBMIT') == 'iso_registry_update' && is_array($arrQuantity) && $objProduct->cart_id)
				{
					$blnReload = true;
					if (!$arrQuantity[$objProduct->cart_id])
					{
						$this->Database->query("DELETE FROM tl_iso_registry_items WHERE id={$objProduct->cart_id}");
					}
					else
					{
						$this->Database->prepare("UPDATE tl_iso_registry_items SET product_quantity=? WHERE id={$objProduct->cart_id}")->executeUncached($arrQuantity[$objProduct->cart_id]);
					}
				}
				
				$arrProductData[] = array_merge($objProduct->getAttributes(), array
				(
					'id'				=> $objProduct->id,
					'image'				=> $objProduct->images->main_image,
					'link'				=> $objProduct->href_reader,
					'price'				=> $this->Isotope->formatPriceWithCurrency($objProduct->price),
					'tax_id'			=> $objProduct->tax_id,
					'quantity'			=> $objProduct->quantity_requested,
					'registry_item_id'	=> $objProduct->cart_id,
					'product_options'	=> $objProduct->getOptions(),
					'remove_link'		=> ampersand($strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?') . 'remove='.$objProduct->cart_id.'&referer='.base64_encode($this->Environment->request)),
					'remove_link_text'  => $GLOBALS['TL_LANG']['MSC']['removeProductLinkText'],
					'remove_link_title' => sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $objProduct->name),
					'class'				=> 'row_' . $i . ($i%2 ? ' even' : ' odd') . ($i==0 ? ' row_first' : ''),
				));
				
				$this->loadLanguageFile('tl_iso_registry');
				
				$objTemplate->registryTitle = $GLOBALS['TL_LANG']['MSC']['registryTitle'];
				$objTemplate->name = $this->Isotope->Registry->name;
				$objTemplate->second_party_name = $this->Isotope->Registry->second_party_name;
				$objTemplate->date = $this->Isotope->Registry->date;
				$objTemplate->event_type = $GLOBALS['TL_LANG']['tl_iso_registry'][$this->Isotope->Registry->event_type];
				$objTemplate->description = $this->Isotope->Registry->description;
				$objTemplate->editLink = ampersand($strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?') . 'action=edit');
				$objTemplate->editText = $GLOBALS['TL_LANG']['MSC']['registryEditText'];
			}
	
			if ($blnReload)
			{
				$this->reload();
			}
			
			if (count($arrProductData))
			{
				$arrProductData[count($arrProductData)-1]['class'] .= ' row_last';
			}
			
			
			$objTemplate->editformId = 'iso_registry_edit';
			$objTemplate->editformSubmit = 'iso_registry_edit';
			$objTemplate->formId = 'iso_registry_update';
			$objTemplate->formSubmit = 'iso_registry_update';
			$objTemplate->action = $this->Environment->request;
			$objTemplate->products = $arrProductData;
			$objTemplate->showOptions = false;	//!@todo make a module option.
			
			$this->Template->registry = $objTemplate->parse();
		}
	}
	
	
	/**
	 * Build the widgets for the input form from the DCA fields
	 * @return array
	 */
	private function getWidgets()
	{
		$this->loadDataContainer('tl_iso_registry');
		$this->loadLanguageFile('tl_iso_registry');
	
		$arrWidgets = array();
		$arrFields = $GLOBALS['TL_DCA']['tl_iso_registry']['fields'];
		
		if(!is_array($arrFields))
		{
			return $arrWidgets;
		}
		
		foreach($arrFields as $k=>$v)
		{	
			$strClass = $GLOBALS['TL_FFL'][$v['inputType']];
			$objWidget = new $strClass($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_registry']['fields'][$k], $k, ''));
			$objWidget->label = $GLOBALS['TL_LANG']['tl_iso_registry']['fields'][$k][0];
			if($v['eval']['mandatory'])
			{
				$objWidget->required = true;
			}
			if($this->Isotope->Registry)
			{
				$objWidget->value = ($v['eval']['rgxp']=='date') ? date("m/d/Y",$this->Isotope->Registry->$k) : $this->Isotope->Registry->$k;
			}
			$arrWidgets[] = $objWidget;
		}
		
		return $arrWidgets;
	}
	
	
	
}

