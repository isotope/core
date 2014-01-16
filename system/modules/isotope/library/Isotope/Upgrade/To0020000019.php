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


class To0020000019 extends \System
{

    public function run($blnInstalled)
    {
        if ($blnInstalled && \Database::getInstance()->tableExists('tl_iso_rule')) {
            foreach (\Database::getInstance()->listFields('tl_iso_rule') as $arrField) {

                if ($arrField['name'] == 'configCondition' && $arrField['type'] == 'char') {
                    \Database::getInstance()->query("UPDATE tl_iso_rule SET configCondition='0' WHERE configCondition='1'");
                    \Database::getInstance()->query("UPDATE tl_iso_rule SET configCondition='1' WHERE configCondition=''");
                }

                if ($arrField['name'] == 'memberCondition' && $arrField['type'] == 'char') {
                    \Database::getInstance()->query("UPDATE tl_iso_rule SET memberCondition='0' WHERE memberCondition='1'");
                    \Database::getInstance()->query("UPDATE tl_iso_rule SET memberCondition='1' WHERE memberCondition=''");
                }

                if ($arrField['name'] == 'productCondition' && $arrField['type'] == 'char') {
                    \Database::getInstance()->query("UPDATE tl_iso_rule SET productCondition='0' WHERE productCondition='1'");
                    \Database::getInstance()->query("UPDATE tl_iso_rule SET productCondition='1' WHERE productCondition=''");
                }
            }
        }
    }
}
