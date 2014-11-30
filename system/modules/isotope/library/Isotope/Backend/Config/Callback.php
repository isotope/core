<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Config;


use Isotope\Automator;
use Isotope\Backend\Permission;
use Isotope\Model\Config;

class Callback extends Permission
{

    /**
     * Check permissions to edit table tl_iso_config
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'configs') {
            return;
        }

        // Set fallback if no fallback is available
        $objConfig = \Database::getInstance()->query("SELECT COUNT(*) AS total FROM tl_iso_config WHERE fallback='1'");

        if ($objConfig->total == 0) {
            $GLOBALS['TL_DCA']['tl_iso_config']['fields']['fallback']['default'] = '1';
        }

        $this->import('BackendUser', 'User');

        // Return if user is admin
        if ($this->User->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_configs) || count($this->User->iso_configs) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $this->User->iso_configs;
        }

        $GLOBALS['TL_DCA']['tl_iso_config']['list']['sorting']['root'] = $root;

        // Check permissions to add configs
        if (!$this->User->hasAccess('create', 'iso_configp')) {
            $GLOBALS['TL_DCA']['tl_iso_config']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_config']['list']['global_operations']['new']);
        }

        // Check current action
        switch (\Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'edit':

                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root)
                    && $this->addNewRecordPermissions(\Input::get('id'), 'iso_configs', 'iso_configp')
                ) {
                    $root[] = \Input::get('id');
                    $this->User->iso_configs = $root;
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_configp'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' store configuration ID "' . \Input::get('id') . '"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_configp')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(\Input::get('act'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' store configurations', __METHOD__, TL_ERROR);
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
        switch ($row['currency']) {
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

        return sprintf('<div class="list_icon" style="background-image:url(\'system/modules/isotope/assets/images/%s.png\');line-height:16px" title="%s">%s</div>', $image, $GLOBALS['TL_LANG']['CUR'][$row['currency']], $label);
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
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_configp')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
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
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_configp')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
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
    protected function doGetTemplateFolders($path, $level = 0)
    {
        $return = array();

        foreach (scan(TL_ROOT . '/' . $path) as $file) {
            if (is_dir(TL_ROOT . '/' . $path . '/' . $file)) {
                $return[$path . '/' . $file] = str_repeat(' &nbsp; &nbsp; ', $level) . $file;
                $return                      = array_merge($return, $this->doGetTemplateFolders($path . '/' . $file, $level + 1));
            }
        }

        return $return;
    }

    /**
     * Store if we need to update the currencies
     * @param   mixed
     * @param   \DataContainer
     */
    public function checkNeedToConvertCurrencies($varValue, \DataContainer $dc)
    {
        $objConfig = Config::findByPk($dc->id);
        if ($objConfig !== null && $varValue != $objConfig->{$dc->field}) {
            $GLOBALS['ISOTOPE_CONFIG_UPDATE_CURRENCIES'] = true;
        }

        return $varValue;
    }

    /**
     * Convert currencies if the settings have changed
     * @param   \DataContainer
     */
    public function convertCurrencies(\DataContainer $dc)
    {
        if ($GLOBALS['ISOTOPE_CONFIG_UPDATE_CURRENCIES']) {
            $objAutomator = new Automator();
            $objAutomator->convertCurrencies($dc->id);
        }
    }
}
