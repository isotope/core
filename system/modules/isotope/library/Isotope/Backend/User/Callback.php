<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\User;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Isotope\Model\Group;


class Callback extends Backend
{

    /**
     * Get groups for checkbox
     * @param   DataContainer
     * @return  array
     */
    public function getGroups($dc)
    {
        $arrGroups = array();

        $this->generateGroups($arrGroups);

        return $arrGroups;
    }

    /**
     * Recursively generate the group options
     * @param   array
     * @param   int
     * @param   int
     */
    protected function generateGroups(&$arrGroups, $intPid = 0, $intLevel = 0)
    {
        $objGroups = Group::findBy('pid', $intPid, array('order' => 'sorting'));

        if (null !== $objGroups) {
            foreach ($objGroups as $objGroup) {
                $arrGroups[$objGroup->id] = str_repeat('&nbsp;&nbsp;', $intLevel * 2 - ($intLevel > 0 ? 1 : 0)) . ($intLevel > 0 ? '&#8627;&nbsp;' : '') . $objGroup->name;

                $this->generateGroups($arrGroups, $objGroup->id, $intLevel + 1);
            }
        }
    }

    public function getMemberGroups()
    {
        $groups = Database::getInstance()->execute("SELECT id, name FROM tl_member_group")->fetchEach('name');

        $result = [-1 => $GLOBALS['TL_LANG']['tl_user']['iso_member_groups'][2]];

        foreach ($groups as $k => $v) {
            $result[$k] = $v;
        }

        return $result;
    }
}
