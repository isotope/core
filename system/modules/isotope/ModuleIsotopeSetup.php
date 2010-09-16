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


class ModuleIsotopeSetup extends BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_iso_setup';
	
	
	public function generate()
	{
		$this->import('BackendUser', 'User');
		
		if ($this->Input->get('table') && is_object($this->objDc))
		{
			$blnAccess = false;
			
			foreach ($GLOBALS['ISO_MOD'] as $strGroup=>$arrModules)
			{
				foreach ($arrModules as $strModule => $arrConfig)
				{
					if (is_array($arrConfig['tables']) && $this->User->hasAccess($strModule, 'iso_modules') && in_array($this->objDc->table, $arrConfig['tables']))
					{
						$blnAccess = true;
						break(2);
					}
				}
			}
			
			if (!$blnAccess)
			{
				$this->log('Table "' . $strTable . '" is not allowed in Isotope module "' . $module . '"', 'ModuleIsotopeSetup generate()', TL_ERROR);
				$this->redirect('contao/main.php?act=error');
			}
			
			$act = $this->Input->get('act');

			if (!strlen($act) || $act == 'paste' || $act == 'select')
			{
				$act = ($this->objDc instanceof listable) ? 'showAll' : 'edit';
			}

			switch ($act)
			{
				case 'delete':
				case 'show':
				case 'showAll':
				case 'undo':
					if (!$this->objDc instanceof listable)
					{
						$this->log('Data container ' . $strTable . ' is not listable', 'Backend getBackendModule()', TL_ERROR);
						trigger_error('The current data container is not listable', E_USER_ERROR);
					}
					break;

				case 'create':
				case 'cut':
				case 'cutAll':
				case 'copy':
				case 'copyAll':
				case 'move':
				case 'edit':
					if (!$this->objDc instanceof editable)
					{
						$this->log('Data container ' . $strTable . ' is not editable', 'Backend getBackendModule()', TL_ERROR);
						trigger_error('The current data container is not editable', E_USER_ERROR);
					}
					break;
			}

			return $this->objDc->$act();
		}
		
		return parent::generate();
	}
	
	
	protected function compile()
	{
		// Modules
		$arrGroups = array();

		foreach ($GLOBALS['ISO_MOD'] as $strGroup=>$arrModules)
		{
			foreach (array_keys($arrModules) as $strModule)
			{
				if ($this->User->hasAccess($strModule, 'iso_modules'))
				{
					$arrGroups[$GLOBALS['TL_LANG']['IMD'][$strGroup]][$GLOBALS['ISO_MOD'][$strGroup][$strModule]['tables'][0]] = array
					(
						'name' => $GLOBALS['TL_LANG']['IMD'][$strModule][0],
						'description' => $GLOBALS['TL_LANG']['IMD'][$strModule][1],
						'icon' => $arrModules[$strModule]['icon']
					);
				}
			}
		}

		$this->Template->arrGroups = $arrGroups;
		$this->Template->script = $this->Environment->script;
		$this->Template->welcome = $GLOBALS['TL_LANG']['ISO']['config_module'];
	}
}

