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
use Contao\Database;
use Contao\Folder;
use Contao\System;

class Callback extends Backend
{
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
