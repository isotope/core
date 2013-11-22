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
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */

namespace Isotope\Backend\Group;

use Isotope\Model\Group;


class Breadcrumb extends \Backend
{

    /**
     * Generate groups breadcrumb and return it as HTML string
     * @param integer
     * @param integer
     * @return string
     */
    public static function generate($intId, $intProductId=null)
    {
        $arrGroups = array();
        $objSession = \Session::getInstance();

        // Set a new gid
        if (isset($_GET['gid']))
        {
            $objSession->set('iso_products_gid', \Input::get('gid'));
            \Controller::redirect(preg_replace('/&gid=[^&]*/', '', \Environment::get('request')));
        }

        // Return if there is no trail
        if (!$objSession->get('iso_products_gid') && !$intProductId)
        {
            return '';
        }

        $objUser = \BackendUser::getInstance();
        $objDatabase = \Database::getInstance();

        // Include the product in variants view
        if ($intProductId)
        {
            $objProduct = $objDatabase->prepare("SELECT gid, name FROM tl_iso_product WHERE id=?")
                                      ->limit(1)
                                      ->execute($intProductId);

            if ($objProduct->numRows)
            {
                $arrGroups[] = array('id'=>$intProductId, 'name'=>$objProduct->name);

                // Override the group ID
                $intId = $objProduct->gid;
            }
        }

        $intPid = $intId;

        // Generate groups
        do
        {
            $objGroup = Group::findByPk($intPid);

            if (null !== $objGroup)
            {
                $arrGroups[] = array('id'=>$objGroup->id, 'name'=>$objGroup->name);

                if ($objGroup->pid)
                {
                    // Do not show the mounted groups
                    if (!$objUser->isAdmin && $objUser->hasAccess($objGroup->id, 'iso_groups'))
                    {
                        break;
                    }

                    $intPid = $objGroup->pid;
                }
            }
        }
        while ($objGroup->pid);

        $arrLinks = array();
        $strUrl = \Environment::get('request');

        // Remove the product ID from URL
        if ($intProductId)
        {
            $strUrl = preg_replace('/&id=[^&]*/', '', $strUrl);
        }

        // Generate breadcrumb trail
        foreach ($arrGroups as $arrGroup)
        {
            if (!$arrGroup['id'])
            {
                continue;
            }

            $buffer = '';

            // No link for the active group
            if ((!$intProductId && $intId == $arrGroup['id']) || ($intProductId && $intProductId == $arrGroup['id']))
            {
                $buffer .= '<img src="system/modules/isotope/assets/images/folder-network.png" width="16" height="16" alt="" style="margin-right:6px;">' . $arrGroup['name'];
            }
            else
            {
                $buffer .= '<img src="system/modules/isotope/assets/images/folder-network.png" width="16" height="16" alt="" style="margin-right:6px;"><a href="' . ampersand($strUrl) . '&amp;gid='.$arrGroup['id'] . '" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['selectGroup']).'">' . $arrGroup['name'] . '</a>';
            }

            $arrLinks[] = $buffer;
        }

        $arrLinks[] = sprintf('<a href="%s" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['allGroups']).'"><img src="system/modules/isotope/assets/images/folders.png" width="16" height="16" alt="" style="margin-right:6px;"> %s</a>', ampersand($strUrl) . '&amp;gid=0', $GLOBALS['TL_LANG']['MSC']['filterAll']);

        return '
<ul id="tl_breadcrumb">
  <li>' . implode(' &gt; </li><li>', array_reverse($arrLinks)) . '</li>
</ul>';
    }
}
