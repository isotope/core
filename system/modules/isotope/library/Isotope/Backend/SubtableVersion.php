<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Backend;


class SubtableVersion extends \Backend
{

    /**
     * Create initial version record if it does not exist
     * @param   string
     * @param   int
     * @param   string
     * @param   array
     */
    public static function initialize($strTable, $intId, $strSubtable, $arrData)
    {
        $objVersion = \Database::getInstance()->prepare("SELECT COUNT(*) AS count FROM tl_version WHERE fromTable=? AND pid=?")
                                     ->limit(1)
                                     ->executeUncached($strSubtable, $intId);

        if ($objVersion->count < 1) {
            static::create($strTable, $intId, $strSubtable, $arrData);
        }
    }

    /**
     * Create a new subtable version record
     * @param   string
     * @param   int
     * @param   string
     * @param   array
     */
    public static function create($strTable, $intId, $strSubtable, $arrData)
    {
        $objVersion = \Database::getInstance()->prepare("SELECT * FROM tl_version WHERE pid=? AND fromTable=? ORDER BY version DESC")
                                     ->limit(1)
                                     ->executeUncached($intId, $strTable);

        // Parent table must have a version
        if ($objVersion->numRows == 0) {
            return;
        }

        \Database::getInstance()->prepare("UPDATE tl_version SET active='' WHERE pid=? AND fromTable=?")
                       ->execute($intId, $strSubtable);

        \Database::getInstance()->prepare("INSERT INTO tl_version (pid, tstamp, version, fromTable, username, userid, description, editUrl, active, data) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)")
                       ->execute($objVersion->pid, $objVersion->tstamp, $objVersion->version, $strSubtable, $objVersion->username, $objVersion->userid, $objVersion->description, $objVersion->editUrl, serialize($arrData));
    }

    /**
     * Find a subtable version record
     * @param   string
     * @param   int
     * @param   string
     */
    public static function find($strTable, $intPid, $intVersion)
    {
        $objVersion = \Database::getInstance()->prepare("SELECT data FROM tl_version WHERE fromTable=? AND pid=? AND version=?")
                                     ->limit(1)
                                     ->execute($strTable, $intPid, $intVersion);

        if (!$objVersion->numRows) {
            return null;
        }

        $arrData = deserialize($objVersion->data);

        if (!is_array($arrData)) {
            return null;
        }

        return $arrData;
    }
}
