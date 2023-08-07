<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;


use Contao\Controller;
use Contao\Database;
use Contao\System;

abstract class Base extends System
{

    /**
     * Create a database field if it does not yet exist
     *
     * @param $strField
     * @param $strTable
     *
     * @return bool     True if field was added, false if it did already exist
     */
    protected function createDatabaseField($strField, $strTable)
    {
        if (!Database::getInstance()->tableExists($strTable, null, true)) {
            return false;
        }

        if (!\Database::getInstance()->fieldExists($strField, $strTable, true)) {
            Database::getInstance()->query("
                ALTER TABLE $strTable
                ADD COLUMN `$strField` " . $this->getSqlForField($strField, $strTable)
            );

            return true;
        }

        return false;
    }

    /**
     * Rename a database field if old one exists and new one does not
     *
     * @param $strOldField
     * @param $strNewField
     * @param $strTable
     *
     * @return bool         True if field was renamed, false if unsuccessful
     */
    protected function renameDatabaseField($strOldField, $strNewField, $strTable)
    {
        if (!Database::getInstance()->tableExists($strTable)) {
            return false;
        }

        if (Database::getInstance()->fieldExists($strOldField, $strTable)
            && !Database::getInstance()->fieldExists($strNewField, $strTable)
        ) {
            Database::getInstance()->query("
                ALTER TABLE $strTable
                CHANGE COLUMN `$strOldField` `$strNewField` " . $this->getSqlForField($strNewField, $strTable)
            );

            return true;
        }

        return false;
    }

    /**
     * Updates a database field to the definition in the DCA.
     *
     * @param string $strField
     * @param string $strTable
     *
     * @return bool
     */
    protected function updateDatabaseField($strField, $strTable)
    {
        if (!\Database::getInstance()->tableExists($strTable)
            || !\Database::getInstance()->fieldExists($strField, $strTable)
        ) {
            return false;
        }

        $statement = Database::getInstance()->prepare("
            ALTER TABLE $strTable
            CHANGE COLUMN `$strField` `$strField` " . $this->getSqlForField($strField, $strTable)
        );

        $statement->execute();

        return $statement->affectedRows > 0;
    }

    /**
     * Get SQL statement for a DCA field
     *
     * @param $strField
     * @param $strTable
     *
     * @return string
     */
    private function getSqlForField($strField, $strTable)
    {
        Controller::loadDataContainer($strTable);
        $strSql = (string) $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['sql'];

        if ($strSql == '') {
            throw new \LogicException('Field "'.$strField.'" is not defined in "'.$strTable.'"');
        }

        return $strSql;
    }
}
