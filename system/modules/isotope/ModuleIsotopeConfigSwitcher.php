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


class ModuleIsotopeConfigSwitcher extends ModuleIsotope
{

	/**
	 * Module template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_configswitcher';


	/**
	 * Generate the module
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: STORE CONFIG SWICHER ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = $this->Environment->script . '?do=' . ((version_compare(VERSION.'.'.BUILD, '2.9.0', '>=')) ? 'themes&amp;table=tl_module' : 'modules') . '&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$this->iso_config_ids = deserialize($this->iso_config_ids);

		if (!is_array($this->iso_config_ids) || !count($this->iso_config_ids))
			return '';

		if (strlen($this->Input->get('config')))
		{
			if (in_array($this->Input->get('config'), $this->iso_config_ids))
			{
				$_SESSION['ISOTOPE']['config_id'] = $this->Input->get('config');
			}

			$this->redirect(preg_replace(('@[?|&]config='.$this->Input->get('config').'@'), '', $this->Environment->request));
		}

		return parent::generate();
	}


	/**
	 * Compile the module
	 */
	protected function compile()
	{
		$this->import('Isotope');

		$arrConfigs = array();
		$objConfigs = $this->Database->execute("SELECT * FROM tl_iso_config WHERE id IN (" . implode(',', $this->iso_config_ids) . ")");

		$c=0;
		while( $objConfigs->next() )
		{
			$arrConfigs[] = array
			(
				'label'		=> (strlen($objConfigs->label) ? $objConfigs->label : $objConfigs->name),
				'class'		=> ($c==0 ? 'first' : ''),
				'active'	=> ($this->Isotope->Config->id == $objConfigs->id ? true : false),
				'href'		=> ($this->Environment->request . (strpos($this->Environment->request, '?')===false ? '?' : '&amp;') . 'config=' . $objConfigs->id),
			);

			$c++;
		}

		$arrConfigs[count($arrConfigs)-1]['class'] = trim($arrConfigs[count($arrConfigs)-1]['class'] . ' last');

		$this->Template->configs = $arrConfigs;
	}
}

