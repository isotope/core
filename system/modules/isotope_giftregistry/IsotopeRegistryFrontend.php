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


class IsotopeRegistryFrontend extends IsotopeFrontend
{
	
	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;
	
	
	public function __construct()
	{
		parent::__construct();
		
		$this->import('Isotope');
	}
	
	
	/**
	 * Callback for add_to_registry button
	 *
	 * @access	public
	 * @param	object
	 * @return	void
	 */
	//!@todo $objModule is always defined, rework to use it and make sure the module config field is in palettes
	public function addToMyRegistry($objProduct, $objModule=null)
	{	
		if(!$this->Isotope->Registry)
		{
			$this->Isotope->Registry = new IsotopeRegistry();
			$this->Isotope->Registry->initializeRegistry();
		}
		$this->Isotope->Registry->addProduct($objProduct, ((is_object($objModule) && $objModule->iso_use_quantity && intval($this->Input->post('quantity_requested')) > 0) ? intval($this->Input->post('quantity_requested')) : 1));
		
		$this->jumpToOrReload($objModule->iso_registry_jumpTo);
	}

	
	/**
	 * Callback for add_to_cart_from_registry button
	 *
	 * @access	public
	 * @param	object, object, int
	 * @return	array
	 */
	
	public function moveToCart($objProduct, $objModule=null)
	{
		$this->Isotope->Registry->transferToCart($this->Isotope->Cart, $objProduct, (intval($this->Input->post('quantity_requested')) > 0) ? intval($this->Input->post('quantity_requested')) : 1, false);
		
		$this->jumpToOrReload($objModule->iso_cart_jumpTo);
		
	}
	
	

	/**
	 * Callback for isoButton Hook.
	 */
	public function registryButton($arrButtons)
	{		
		if (TL_MODE == 'FE' && !$this->registryExists())
		{
			return $arrButtons;
		}

		$arrButtons['registry'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['registry'], 'callback'=>array('IsotopeRegistryFrontend', 'addToMyRegistry'));
		
		return $arrButtons;
	}
	
	
	
	
	
	/**
	 * Callback for isoButton Hook.
	 */
	public function registryCartButton($arrButtons)
	{	

		$arrButtons['registryCart'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['registryCart'], 'callback'=>array('IsotopeRegistryFrontend', 'moveToCart'));
		
		return $arrButtons;
	}

	
	
	/**
	 * Callback for Checkout Shipping Address Process
	 */
	public function registryAddress($arrOptions, $field, $objModule)
	{		
		if($field=='shipping_address' && $objModule->type='iso_checkout')
		{	
		
			//Determine whether products in the cart are from a registry
			$objItems = $this->Database->prepare("SELECT registry_id FROM tl_iso_cart_items WHERE pid=?")->execute($this->Isotope->Cart->id);
			while($objItems->next())
			{
				if($objItems->registry_id)
				{
					$objRegistry = new IsotopeRegistry();
					$objRegistry->findBy('id',$objItems->registry_id);
					$arrAddress = $objRegistry->shippingAddress;

					if (!in_array($arrAddress['country'],  $this->Isotope->Config->shipping_countries))
						continue;
							
					$arrAddresses[$arrAddress['id']] = array
					(
						'value'		=> $arrAddress['id'],
						'label'		=> $GLOBALS['TL_LANG']['MSC']['shipToRegistry'] . '<br /><span>' . $this->Isotope->generateAddressString($arrAddress, $this->Isotope->Config->shipping_fields) . '</span>',
					);
				}
			}
		}
		
		if(count($arrAddresses))
		{
			foreach($arrAddresses as $address)
				$arrOptions[] = $address;
		}
		
		return $arrOptions;
	}
	
	
	/**
	 * Check if the logged in user has a registry or not
	 * @return bool
	 */
	public function registryExists()
	{
		$this->import('FrontendUser', 'User');
		$blnExists = false;
		if (!FE_USER_LOGGED_IN)
		{
			return $blnExists;
		}
		$objRegistries = $this->Database->execute("SELECT * FROM tl_iso_registry WHERE pid={$this->User->id}");
		if($objRegistries->numRows)
		{
			if(!$this->Isotope->Registry)
			{
				$this->Isotope->Registry = new IsotopeRegistry();
				$this->Isotope->Registry->initializeRegistry();
			}
			$blnExists = true;
		}
		return $blnExists;
	}
	
}