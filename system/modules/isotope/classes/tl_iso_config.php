<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope;


/**
 * Class tl_iso_config
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_config extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_config
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'configs')
        {
            return;
        }

        // Set fallback if no fallback is available
        $objConfig = \Database::getInstance()->query("SELECT COUNT(*) AS total FROM tl_iso_config WHERE fallback='1'");

        if ($objConfig->total == 0)
        {
            $GLOBALS['TL_DCA']['tl_iso_config']['fields']['fallback']['default'] = '1';
        }

        $this->import('BackendUser', 'User');

        // Return if user is admin
        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_configs) || count($this->User->iso_configs) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->iso_configs;
        }

        $GLOBALS['TL_DCA']['tl_iso_config']['list']['sorting']['root'] = $root;

        // Check permissions to add configs
        if (!$this->User->hasAccess('create', 'iso_configp'))
        {
            $GLOBALS['TL_DCA']['tl_iso_config']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_config']['list']['global_operations']['new']);
        }

        // Check current action
        switch (\Input::get('act'))
        {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root))
                {
                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_iso_config']) && in_array(\Input::get('id'), $arrNew['tl_iso_config']))
                    {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0])
                        {
                            $objUser = \Database::getInstance()->prepare("SELECT iso_configs, iso_configp FROM tl_user WHERE id=?")
                                                               ->limit(1)
                                                               ->execute($this->User->id);

                            $arrPermissions = deserialize($objUser->iso_configp);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objUser->iso_configs);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user SET iso_configs=? WHERE id=?")
                                                        ->execute(serialize($arrAccess), $this->User->id);
                            }
                        }

                        // Add permissions on group level
                        elseif ($this->User->groups[0] > 0)
                        {
                            $objGroup = \Database::getInstance()->prepare("SELECT iso_configs, iso_configp FROM tl_user_group WHERE id=?")
                                                                ->limit(1)
                                                                ->execute($this->User->groups[0]);

                            $arrPermissions = deserialize($objGroup->iso_configp);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objGroup->iso_configs);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user_group SET iso_configs=? WHERE id=?")
                                                        ->execute(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = \Input::get('id');
                        $this->User->iso_configs = $root;
                    }
                }
                // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_configp')))
                {
                    \System::log('Not enough permissions to '.\Input::get('act').' store configuration ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_configp'))
                {
                    $session['CURRENT']['IDS'] = array();
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(\Input::get('act')))
                {
                    \System::log('Not enough permissions to '.\Input::get('act').' store configurations', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Add an image to each record
     * @param array
     * @param string
     * @return string
     */
    public function addIcon($row, $label)
    {
        switch ($row['currency'])
        {
            case 'AUD':
                $image = 'currency-dollar-aud';
                break;

            case 'CAD':
                $image = 'currency-dollar-cad';
                break;

            case 'NZD':
                $image = 'currency-dollar-nzd';
                break;

            case 'USD':
                $image = 'currency-dollar-usd';
                break;

            case 'BBD':
            case 'BMD':
            case 'BND':
            case 'BSD':
            case 'BZD':
            case 'FJD':
            case 'GYD':
            case 'HKD':
            case 'JMD':
            case 'KYD':
            case 'LRD':
            case 'MYR':
            case 'NAD':
            case 'SBD':
            case 'SGD':
            case 'SRD':
            case 'TTD':
            case 'TWD':
            case 'ZWL':
                $image = 'currency';
                break;

            case 'EUR':
                $image = 'currency-euro';
                break;

            case 'EGP':
            case 'FKP':
            case 'GBP':
            case 'GIP':
            case 'LBP':
            case 'SDG':
            case 'SHP':
            case 'SYP':
                $image = 'currency-pound';
                break;

            case 'BYR':
            case 'RUB':
                $image = 'currency-ruble';
                break;

            case 'JPY':
                $image = 'currency-yen';
                break;

            default:
                $image = 'money';
        }

        return sprintf('<div class="list_icon" style="background-image:url(\'system/modules/isotope/assets/%s.png\');line-height:16px" title="%s">%s</div>', $image, $GLOBALS['TL_LANG']['CUR'][$row['currency']], $label);
    }


    /**
     * Return the copy config button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyConfig($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_configp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }


    /**
     * Return the delete config button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteConfig($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_configp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }


    /**
     * Return the file picker wizard
     * @param DataContainer
     * @return string
     */
    public function filePicker(\DataContainer $dc)
    {
        $strField = 'ctrl_' . $dc->field . ((\Input::get('act') == 'editAll') ? '_' . $dc->id : '');

        return ' ' . \Image::getHtml('pickfile.gif', $GLOBALS['TL_LANG']['MSC']['filepicker'], 'style="vertical-align:top;cursor:pointer" onclick="Backend.pickFile(\'' . $strField . '\')"');
    }


	/**
	 * Return all template folders as array
	 * @return array
	 */
	public function getTemplateFolders()
	{
		return $this->doGetTemplateFolders('templates');
	}


	/**
	 * Return all template folders as array
	 * @param string
	 * @param integer
	 * @return array
	 */
	protected function doGetTemplateFolders($path, $level=0)
	{
		$return = array();

		foreach (scan(TL_ROOT . '/' . $path) as $file)
		{
			if (is_dir(TL_ROOT . '/' . $path . '/' . $file))
			{
				$return[$path . '/' . $file] = str_repeat(' &nbsp; &nbsp; ', $level) . $file;
				$return = array_merge($return, $this->doGetTemplateFolders($path . '/' . $file, $level+1));
			}
		}

		return $return;
	}


    /**
     * Load URL matrix for multiColumnWizard
     * @param   mixed
     * @param   DataContainer
     */
    public function loadUrlMatrix($varValue, $dc)
    {
        $arrReturn = array();
        $arrParams = $GLOBALS['TL_DCA']['tl_iso_config']['fields']['urlMatrix']['eval']['urlParams'];
        $varValue = deserialize($varValue);

        if (!is_array($varValue)) {
            $varValue = array();
        }

        foreach ($arrParams as $strParam) {
            $arrReturn[] = array(
                'original'  => $strParam,
                'custom'    => (isset($varValue[$strParam]) && $varValue[$strParam] != $strParam) ? $varValue[$strParam] : $strParam
            );
        }

        return $arrReturn;
    }


    /**
     * Save URL matrix for multiColumnWizard
     * @param   mixed
     * @param   DataContainer
     */
    public function saveUrlMatrix($varValue, $dc)
    {
        $varValue = deserialize($varValue);
        $arrReturn = array();
        $arrParams = $GLOBALS['TL_DCA']['tl_iso_config']['fields']['urlMatrix']['eval']['urlParams'];

        foreach ($arrParams as $k => $strParam) {
            $arrReturn[$strParam] = $varValue[$k]['custom'];
        }

        return serialize($arrReturn);
    }
}
