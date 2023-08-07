<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Database;
use Contao\Session;
use Contao\StringUtil;

class Permission extends Backend
{
    /**
     * Add access permission for a record to the backend user
     *
     * @param int    $id
     * @param string $table
     * @param string $accessField
     * @param string $permissionField
     *
     * @return bool If current record in a new record
     */
    protected function addNewRecordPermissions($id, $table, $accessField, $permissionField)
    {
        $user    = BackendUser::getInstance();
        $session = Session::getInstance();
        $db      = Database::getInstance();
        $groups  = StringUtil::deserialize($user->groups);

        $newRecords = $session->get('new_records');

        if (\is_array($newRecords[$table]) && \in_array($id, $newRecords[$table])) {

            if ('custom' === $user->inherit || empty($groups)) {
                // Add permissions on user level

                $objUser = $db->prepare(
                    "SELECT id, $accessField, $permissionField FROM tl_user WHERE id=?"
                )->execute($user->id);

                $this->addCreatePermission($id, $permissionField, $accessField, 'tl_user', $objUser);

            } elseif (!empty($groups) && \is_array($groups)) {
                // Add permissions on group level

                $objGroups = $db->execute("
                    SELECT id, $accessField, $permissionField
                    FROM tl_user_group
                    WHERE " . $db->findInSet('id', $groups)
                );

                while ($objGroups->next()) {
                    if ($this->addCreatePermission($id, $permissionField, $accessField, 'tl_user_group', $objGroups)) {
                        break;
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Update permissions in database if user has "create" access
     *
     * @param string $permissionField
     * @param string $accessField
     * @param string $table
     * @param object $record
     *
     * @return bool
     */
    private function addCreatePermission($id, $permissionField, $accessField, $table, $record)
    {
        $arrPermissions = StringUtil::deserialize($record->$permissionField);

        if (\is_array($arrPermissions) && \in_array('create', $arrPermissions, true)) {
            $arrAccess   = StringUtil::deserialize($record->$accessField);
            $arrAccess[] = $id;
            $arrAccess   = array_unique($arrAccess);

            Database::getInstance()->prepare(
                "UPDATE $table SET $accessField=? WHERE id=?"
            )->execute(serialize($arrAccess), $record->id);

            return true;
        }

        return false;
    }
}
