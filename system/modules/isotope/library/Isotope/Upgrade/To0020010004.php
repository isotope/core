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

namespace Isotope\Upgrade;


class To0020010004 extends \System
{

    public function run($blnInstalled)
    {
        if ($blnInstalled
            && \Database::getInstance()->fieldExists('tableless', 'tl_module')
            && \Database::getInstance()->fieldExists('tableless', 'tl_form')
        ) {

            $objModules = \Database::getInstance()->query("
                SELECT iso_order_conditions, tableless FROM tl_module WHERE iso_order_conditions>0
            ");

            while ($objModules->next()) {
                \Database::getInstance()->prepare("UPDATE tl_form SET tableless=? WHERE id=?")->execute(
                    $objModules->tableless,
                    $objModules->iso_order_conditions
                );
            }
        }
    }
}
