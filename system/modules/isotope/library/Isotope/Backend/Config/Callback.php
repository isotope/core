<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Config;

use Contao\Backend;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Isotope\Automator;
use Isotope\Backend\Permission;
use Isotope\Model\Config;

class Callback extends Permission
{
    /**
     * Check permissions to edit table tl_iso_config
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('configs' !== Input::get('mod')) {
            return;
        }

        // Set fallback if no fallback is available
        $objConfig = Database::getInstance()->query("SELECT COUNT(*) AS total FROM tl_iso_config WHERE fallback='1'");

        if ($objConfig->total == 0) {
            $GLOBALS['TL_DCA']['tl_iso_config']['fields']['fallback']['default'] = '1';
        }

        $this->import('BackendUser', 'User');

        // Return if user is admin
        if ($this->User->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($this->User->iso_configs) || \count($this->User->iso_configs) < 1) {
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
        switch (Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'edit':

                // Dynamically add the record to the user profile
                if (!\in_array(Input::get('id'), $root)
                    && $this->addNewRecordPermissions(Input::get('id'), 'tl_iso_config', 'iso_configs', 'iso_configp')
                ) {
                    $root[] = Input::get('id');
                    $this->User->iso_configs = $root;
                }
                // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'), $root) || ('delete' === Input::get('act') && !$this->User->hasAccess('delete', 'iso_configp'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' store configuration ID "' . Input::get('id') . '"');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = Session::getInstance()->getData();
                if ('deleteAll' === Input::get('act') && !$this->User->hasAccess('delete', 'iso_configp')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                Session::getInstance()->setData($session);
                break;

            default:
                if (\strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' store configurations');
                }
                break;
        }
    }

    /**
     * Add an image to each record
     *
     * @param array  $row
     * @param string $label
     *
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

        $style = 'background-image:url(\'system/modules/isotope/assets/images/' . $image . '.png\');line-height:16px';

        return sprintf('<div class="list_icon" style="%s" title="%s">%s</div>', $style, $GLOBALS['TL_LANG']['CUR'][$row['currency']], $label);
    }

    /**
     * Return the copy config button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function copyConfig($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_configp')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    /**
     * Return the delete config button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deleteConfig($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_configp')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    /**
     * Return the file picker wizard
     *
     *
     * @return string
     */
    public function filePicker(DataContainer $dc)
    {
        $strField = 'ctrl_' . $dc->field . (('editAll' === Input::get('act')) ? '_' . $dc->id : '');

        return ' ' . Image::getHtml('pickfile.svg', $GLOBALS['TL_LANG']['MSC']['filepicker'], 'style="vertical-align:top;cursor:pointer" onclick="Backend.pickFile(\'' . $strField . '\')"');
    }

    /**
     * Return all template folders as array
     *
     * @return array
     */
    public function getTemplateFolders()
    {
        return $this->doGetTemplateFolders('templates');
    }

    /**
     * Generate an options list of order details frontend modules
     *
     * @return array
     */
    public function getOrderDetailsModules()
    {
        $modules = [];
        $result  = Database::getInstance()->query("
            SELECT m.id, m.name, t.name AS theme
            FROM tl_module m
            JOIN tl_theme t ON t.id=m.pid
            WHERE m.type='iso_orderdetails'
            ORDER BY theme, name
        ");

        while ($result->next()) {
            $modules[$result->theme][$result->id] = $result->name;
        }

        return $modules;
    }

    /**
     * Return all template folders as array
     *
     * @param string $path
     * @param int    $level
     *
     * @return array
     */
    protected function doGetTemplateFolders($path, $level = 0)
    {
        $return = array();

        foreach (\Contao\Folder::scan(TL_ROOT . '/' . $path) as $file) {
            if (is_dir(TL_ROOT . '/' . $path . '/' . $file)) {
                $return[$path . '/' . $file] = str_repeat(' &nbsp; &nbsp; ', $level) . $file;
                $return                      = array_merge($return, $this->doGetTemplateFolders($path . '/' . $file, $level + 1));
            }
        }

        return $return;
    }

    /**
     * Store if we need to update the currencies
     *
     * @param mixed          $varValue
     *
     * @return mixed
     */
    public function checkNeedToConvertCurrencies($varValue, DataContainer $dc)
    {
        $objConfig = Config::findByPk($dc->id);
        if ($objConfig !== null && $varValue != $objConfig->{$dc->field}) {
            $GLOBALS['ISOTOPE_CONFIG_UPDATE_CURRENCIES'] = true;
        }

        return $varValue;
    }

    /**
     * Convert currencies if the settings have changed
     */
    public function convertCurrencies(DataContainer $dc)
    {
        if ($GLOBALS['ISOTOPE_CONFIG_UPDATE_CURRENCIES'] ?? false) {
            $objAutomator = new Automator();
            $objAutomator->convertCurrencies($dc->id);
        }
    }
}
