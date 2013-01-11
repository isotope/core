<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\BackendModule;


/**
 * Class ModuleIsotopeSetup
 *
 * Back end module Isotope "setup".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Setup extends \BackendModule
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


    /**
     * Generate the module
     * @return string
     */
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
                        'name' => ($GLOBALS['TL_LANG']['IMD'][$strModule][0] ? $GLOBALS['TL_LANG']['IMD'][$strModule][0] : $strModule),
                        'description' => $GLOBALS['TL_LANG']['IMD'][$strModule][1],
                        'icon' => $arrConfig['icon']
                    );
                }
            }
        }

        // Open module
        if (\Input::get('mod') != '')
        {
            return $this->getIsotopeModule(\Input::get('mod'));
        }

        // Table set but module missing, fix the saveNcreate link
        elseif (\Input::get('table') != '')
        {
            foreach ($GLOBALS['ISO_MOD'] as $arrGroup)
            {
                foreach( $arrGroup as $strModule => $arrConfig )
                {
                    if (is_array($arrConfig['tables']) && in_array(\Input::get('table'), $arrConfig['tables']))
                    {
                        $this->redirect($this->addToUrl('mod=' . $strModule));
                    }
                }
            }
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template->modules = $this->arrModules;
        $this->Template->script = $this->Environment->script;
        $this->Template->welcome = sprintf($GLOBALS['TL_LANG']['ISO']['config_module'], ISO_VERSION . '.' . ISO_BUILD);
    }


    /**
     * Open an isotope module and return it as HTML
     * @param string
     * @return mixed
     */
    protected function getIsotopeModule($module)
    {
        $arrModule = array();

        foreach ($GLOBALS['ISO_MOD'] as $arrGroup)
        {
            if (!empty($arrGroup) && in_array($module, array_keys($arrGroup)))
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

        $strTable = \Input::get('table');

        if ($strTable == '' && $arrModule['callback'] == '')
        {
            $this->redirect($this->addToUrl('table='.$arrModule['tables'][0]));
        }

        $id = (!\Input::get('act') && \Input::get('id')) ? \Input::get('id') : $this->Session->get('CURRENT_ID');

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
        if ($strTable != '')
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
        elseif (\Input::get('key') && isset($arrModule[\Input::get('key')]))
        {
            $objCallback = new $arrModule[\Input::get('key')][0]();

            return $objCallback->$arrModule[\Input::get('key')][1]($dc, $strTable, $arrModule);
        }

        // Default action
        elseif (is_object($dc))
        {
            $act = \Input::get('act');

            if (!strlen($act) || $act == 'paste' || $act == 'select')
            {
                $act = ($dc instanceof \listable) ? 'showAll' : 'edit';
            }

            switch ($act)
            {
                case 'delete':
                case 'show':
                case 'showAll':
                case 'undo':
                    if (!$dc instanceof \listable)
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
                    if (!$dc instanceof \editable)
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
