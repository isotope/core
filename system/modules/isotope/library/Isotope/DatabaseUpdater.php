<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\Database;
use Database\Installer;

/**
 * DatabaseUpdater automatically performs safe or necessary database updates on config changes.
 * Safe changes include adding new fields, altering field config and dropping indexes.
 */
class DatabaseUpdater extends Installer
{

    /**
     * Automatically add and update columns and keys
     * @param    array
     */
    public function autoUpdateTables($arrTables)
    {
        $arrCommands = $this->compileCommands();

        foreach ($arrTables as $strTable) {

            if (!empty($arrCommands['ALTER_DROP']) && \is_array($arrCommands['ALTER_DROP'])) {
                foreach ($arrCommands['ALTER_DROP'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '` DROP INDEX') === 0) {
                        Database::getInstance()->query($strCommand);
                    }
                }
            }

            if (!empty($arrCommands['ALTER_CHANGE']) && \is_array($arrCommands['ALTER_CHANGE'])) {
                foreach ($arrCommands['ALTER_CHANGE'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '`') === 0) {
                        // Try to fix string to int field conversion
                        if (preg_match('/`([^`]+)` int\(10\) NOT NULL default 0;?$/i', $strCommand, $match)) {
                            Database::getInstance()->query("UPDATE `$strTable` SET `$match[1]`=0 WHERE `$match[1]`=''");
                        }

                        Database::getInstance()->query($strCommand);
                    }
                }
            }

            if (!empty($arrCommands['ALTER_ADD']) && \is_array($arrCommands['ALTER_ADD'])) {
                foreach ($arrCommands['ALTER_ADD'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '`') === 0) {
                        Database::getInstance()->query($strCommand);
                    }
                }
            }
        }
    }
}
