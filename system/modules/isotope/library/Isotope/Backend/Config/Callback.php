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
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Folder;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Isotope\Automator;
use Isotope\Backend\Permission;
use Isotope\CompatibilityHelper;
use Isotope\Model\Config;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class Callback extends Backend
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

        $user = BackendUser::getInstance();

        // Return if user is admin
        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($user->iso_configs) || \count($user->iso_configs) < 1) {
            $root = array(0);
        } else {
            $root = $user->iso_configs;
        }

        $GLOBALS['TL_DCA']['tl_iso_config']['list']['sorting']['root'] = $root;
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
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        foreach (Folder::scan($projectDir . '/' . $path) as $file) {
            if (is_dir($projectDir . '/' . $path . '/' . $file)) {
                $return[$path . '/' . $file] = str_repeat(' &nbsp; &nbsp; ', $level) . $file;
                $return                      = array_merge($return, $this->doGetTemplateFolders($path . '/' . $file, $level + 1));
            }
        }

        return $return;
    }
}
