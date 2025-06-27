<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\OrderStatus;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;

class Callback extends Backend
{
    /**
     * Gets notification options for order status change.
     *
     * @return array
     */
    public function getNotificationChoices(DataContainer $dc)
    {
        $arrChoices = array();
        $objNotifications = Database::getInstance()->execute(
            "SELECT id,title FROM tl_nc_notification WHERE type='iso_order_status_change' ORDER BY title"
        );

        while ($objNotifications->next()) {
            $arrChoices[$objNotifications->id] = $objNotifications->title;
        }

        return $arrChoices;
    }
}
