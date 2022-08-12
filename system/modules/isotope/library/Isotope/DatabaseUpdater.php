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
use Contao\System;
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
        $container = System::getContainer();

        $installer = $container->has('contao_installation.database.installer')
            ? $container->get('contao_installation.database.installer')
            : $container->get('contao.installer');

        $arrCommands = $installer->getCommands();

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
