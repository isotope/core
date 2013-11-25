<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;


/**
 * Class Isotope\DatabaseUpdater
 *
 * Provide methods to send Isotope e-mails.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class DatabaseUpdater extends \Database\Installer
{

    /**
     * Automatically add and update columns and keys
     * @param    array
     */
    public function autoUpdateTables($arrTables)
    {
        $arrCommands = $this->compileCommands();

        foreach ($arrTables as $strTable) {

            if (!empty($arrCommands['ALTER_DROP']) && is_array($arrCommands['ALTER_DROP'])) {
                foreach ($arrCommands['ALTER_DROP'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '` DROP INDEX') === 0) {
                        \Database::getInstance()->query($strCommand);
                    }
                }
            }

            if (!empty($arrCommands['ALTER_CHANGE']) && is_array($arrCommands['ALTER_CHANGE'])) {
                foreach ($arrCommands['ALTER_CHANGE'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '`') === 0) {
                        \Database::getInstance()->query($strCommand);
                    }
                }
            }

            if (!empty($arrCommands['ALTER_ADD']) && is_array($arrCommands['ALTER_ADD'])) {
                foreach ($arrCommands['ALTER_ADD'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '`') === 0) {
                        \Database::getInstance()->query($strCommand);
                    }
                }
            }
        }
    }
}
