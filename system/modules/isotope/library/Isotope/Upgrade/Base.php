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


abstract class Base extends \System
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
        if (!\Database::getInstance()->fieldExists($strField, $strTable)) {
            \Database::getInstance()->query("
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
        if (\Database::getInstance()->fieldExists($strOldField, $strTable)
            && !\Database::getInstance()->fieldExists($strNewField, $strTable)
        ) {
            \Database::getInstance()->query("
                ALTER TABLE $strTable
                CHANGE COLUMN `$strOldField` `$strNewField` " . $this->getSqlForField($strNewField, $strTable)
            );

            return true;
        }

        return false;
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
        \Controller::loadDataContainer($strTable);
        $strSql = (string) $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['sql'];

        if ($strSql == '') {
            throw new \LogicException('Field "'.$strField.'" is not defined in "'.$strTable.'"');
        }

        return $strSql;
    }
} 