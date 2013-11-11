<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope\Backend\User;

use Isotope\Model\Group;


class Callback extends \Backend
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
    protected function generateGroups(&$arrGroups, $intPid=0, $intLevel=0)
    {
        $objGroups = Group::findBy('pid', $intPid, array('order'=>'sorting'));

        if (null !== $objGroups) {
            foreach ($objGroups as $objGroup) {
                $arrGroups[$objGroup->id] = str_repeat('&nbsp;&nbsp;', $intLevel*2 - ($intLevel>0 ? 1 : 0)) . ($intLevel > 0 ? '&#8627;&nbsp;' : '') . $objGroup->name;

                $this->generateGroups($arrGroups, $objGroup->id, $intLevel+1);
            }
        }
    }
}
