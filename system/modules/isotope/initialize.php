<?php
/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

foreach ([
    '../../initialize.php',
    '../../../../../../../system/initialize.php',
    '../../../../../../system/initialize.php',
         ] as $script
) {
    if (file_exists($script)) {
        require_once($script);
        return;
    }
}

die('Contao initialize.php was not found');
