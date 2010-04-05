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


class ModuleIsotopeStoreSwitcher extends ModuleIsotope
{
	
	/**
	 * Module template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_storeswitcher';
	
	
	/**
	 * Generate the module
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE STORE SWICHER ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->store_ids = deserialize($this->store_ids);
		
		if (!is_array($this->store_ids) || !count($this->store_ids))
			return '';
			
		if (strlen($this->Input->get('store')))
		{
			if (in_array($this->Input->get('store'), $this->store_ids))
			{
				$_SESSION['isotope']['store_id'] = $this->Input->get('store');
			}
			
			$this->redirect(preg_replace(('@[?|&]store='.$this->Input->get('store').'@'), '', $this->Environment->request));
		}
		
		return parent::generate();
	}
	
	
	/**
	 * Compile the module
	 */
	protected function compile()
	{
		$this->import('Isotope');
		
		$arrStores = array();
		$objStores = $this->Database->execute("SELECT * FROM tl_store WHERE id IN (" . implode(',', $this->store_ids) . ")");
		
		$c=0;
		while( $objStores->next() )
		{
			$arrStores[] = array
			(
				'label'		=> (strlen($objStores->label) ? $objStores->label : $objStores->store_configuration_name),
				'class'		=> ($c==0 ? 'first' : ''),
				'active'	=> ($this->Isotope->Store->id == $objStores->id ? true : false),
				'href'		=> ($this->Environment->request . (strpos($this->Environment->request, '?')===false ? '?' : '&amp;') . 'store=' . $objStores->id),
			);
			
			$c++;
		}
		
		$arrStores[count($arrStores)-1]['class'] = trim($arrStores[count($arrStores)-1]['class'] . ' last');
		
		$this->Template->stores = $arrStores;
	}
}

