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
}
