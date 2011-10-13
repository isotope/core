<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleIsotopeSetup extends BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_iso_setup';


	/**
	 * Isotope modules
	 * @var array
	 */
	protected $arrModules = array();


	public function generate()
	{
		$this->import('BackendUser', 'User');

		foreach ($GLOBALS['ISO_MOD'] as $strGroup => $arrModules)
		{
			foreach ($arrModules as $strModule => $arrConfig)
			{
				if ($this->User->hasAccess($strModule, 'iso_modules'))
				{
					if (is_array($arrConfig['tables']))
					{
						$GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'] += $arrConfig['tables'];
					}

					$this->arrModules[$GLOBALS['TL_LANG']['IMD'][$strGroup]][$strModule] = array
					(
						'name' => $GLOBALS['TL_LANG']['IMD'][$strModule][0],
						'description' => $GLOBALS['TL_LANG']['IMD'][$strModule][1],
						'icon' => $arrConfig['icon']
					);
				}
			}
		}

		// Open module
		if ($this->Input->get('mod'))
		{
			return $this->getIsotopeModule($this->Input->get('mod'));
		}

		return parent::generate();
	}


	protected function compile()
	{
		$this->Template->modules = $this->arrModules;
		$this->Template->script = $this->Environment->script;
		$this->Template->welcome = $GLOBALS['TL_LANG']['ISO']['config_module'];
	}


	/**
	 * Open an isotope module and return it as HTML
	 * @param string
	 * @return string
	 */
	protected function getIsotopeModule($module)
	{
		$arrModule = array();

		foreach ($GLOBALS['ISO_MOD'] as $arrGroup)
		{
			if (count($arrGroup) && in_array($module, array_keys($arrGroup)))
			{
				$arrModule =& $arrGroup[$module];
			}
		}

		// Check whether the current user has access to the current module
		if (!$this->User->isAdmin && !$this->User->hasAccess($module, 'iso_modules'))
		{
			$this->log('Isotope module "' . $module . '" was not allowed for user "' . $this->User->username . '"', 'ModuleIsotopeSetup getIsotopeModule()', TL_ERROR);
			$this->redirect($this->Environment->script.'?act=error');
		}

		$strTable = $this->Input->get('table');

		if ($strTable == '' && $arrModule['callback'] == '')
		{
			$this->redirect($this->addToUrl('table='.$arrModule['tables'][0]));
		}

		$id = (!$this->Input->get('act') && $this->Input->get('id')) ? $this->Input->get('id') : $this->Session->get('CURRENT_ID');

		// Add module style sheet
		if (isset($arrModule['stylesheet']))
		{
			$GLOBALS['TL_CSS'][] = $arrModule['stylesheet'];
		}

		// Add module javascript
		if (isset($arrModule['javascript']))
		{
			$GLOBALS['TL_JAVASCRIPT'][] = $arrModule['javascript'];
		}

		// Redirect if the current table does not belong to the current module
		if (strlen($strTable))
		{
			if (!in_array($strTable, (array) $arrModule['tables']))
			{
				$this->log('Table "' . $strTable . '" is not allowed in Isotope module "' . $module . '"', 'ModuleIsotopeSetup getIsotopeModule()', TL_ERROR);
				$this->redirect('contao/main.php?act=error');
			}

			// Load the language and DCA file
			$this->loadLanguageFile($strTable);
			$this->loadDataContainer($strTable);

			// Include all excluded fields which are allowed for the current user
			if ($GLOBALS['TL_DCA'][$strTable]['fields'])
			{
				foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $k=>$v)
				{
					if ($v['exclude'])
					{
						if ($this->User->hasAccess($strTable.'::'.$k, 'alexf'))
						{
							$GLOBALS['TL_DCA'][$strTable]['fields'][$k]['exclude'] = false;
						}
					}
				}
			}

			// Fabricate a new data container object
			if (!strlen($GLOBALS['TL_DCA'][$strTable]['config']['dataContainer']))
			{
				$this->log('Missing data container for table "' . $strTable . '"', 'Backend getBackendModule()', TL_ERROR);
				trigger_error('Could not create a data container object', E_USER_ERROR);
			}

			$dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
			require_once(sprintf('%s/system/drivers/%s.php', TL_ROOT, $dataContainer));

			$dc = new $dataContainer($strTable);
		}

		// AJAX request
		if ($_POST && $this->Environment->isAjaxRequest)
		{
			$this->objAjax->executePostActions($dc);
		}

		// Call module callback
		elseif ($this->classFileExists($arrModule['callback']))
		{
			$objCallback = new $arrModule['callback']($dc);
			return $objCallback->generate();
		}

		// Custom action (if key is not defined in config.php the default action will be called)
		elseif ($this->Input->get('key') && isset($arrModule[$this->Input->get('key')]))
		{
			$objCallback = new $arrModule[$this->Input->get('key')][0]();
			return $objCallback->$arrModule[$this->Input->get('key')][1]($dc, $strTable, $arrModule);
		}

		// Default action
		elseif (is_object($dc))
		{
			$act = $this->Input->get('act');

			if (!strlen($act) || $act == 'paste' || $act == 'select')
			{
				$act = ($dc instanceof listable) ? 'showAll' : 'edit';
			}

			switch ($act)
			{
				case 'delete':
				case 'show':
				case 'showAll':
				case 'undo':
					if (!$dc instanceof listable)
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
					if (!$dc instanceof editable)
					{
						$this->log('Data container ' . $strTable . ' is not editable', 'Backend getBackendModule()', TL_ERROR);
						trigger_error('The current data container is not editable', E_USER_ERROR);
					}
					break;
			}

			return $dc->$act();
		}

		return null;
	}
}

