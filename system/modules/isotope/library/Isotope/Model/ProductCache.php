<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;

/**
 * Isotope\Model\ProductCache represents an Isotope product cache model
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductCache extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_productcache';


    /**
     * Return sorted and serialized list of active member groups for cache lookup
     * @return  string
     */
    public static function getCacheableGroups()
    {
        static $groups = null;

        if (null === $groups) {
            $groups = '';

            if (FE_USER_LOGGED_IN === true)
            {
                $arrGroups = \FrontendUser::getInstance()->groups;

                if (!empty($arrGroups) && is_array($arrGroups))
                {
                    // Make sure groups array always looks the same to find it in the database
                    $arrGroups = array_unique($arrGroups);
                    sort($arrGroups, SORT_NUMERIC);
                    $groups = serialize($arrGroups);
                }
            }
        }

        return $groups;
    }
}
