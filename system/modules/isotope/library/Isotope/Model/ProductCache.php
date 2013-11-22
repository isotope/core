<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
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
     * Get array of product IDs
     * @return  array
     */
    public function getProductIds()
    {
        if ($this->products == '') {
            return array();
        }

        return array_map('intval', explode(',', $this->products));
    }

    /**
     * Set array of products IDs for this cache
     * @param   array
     * @return  ProductCache
     */
    public function setProductIds(array $arrIds)
    {
        $this->products = implode(',', array_map('intval', $arrIds));

        return $this;
    }

    /**
     * Find cache for module on page (including current environment)
     * @param   int
     * @param   int
     * @return  ProductCache|null
     */
    public static function findForPageAndModule($intPage, $intModule, array $arrOptions=array())
    {
        return static::findOneBy(
            array(
                'page_id=?',
                'module_id=?',
                'requestcache_id=?',
                'groups=?',
                "(keywords=? OR keywords='')",
                '(expires>? OR expires=0)'
            ),
            array($intPage, $intModule, (int) \Input::get('isorc'), static::getCacheableGroups(), (string) \Input::get('keywords'), time()),
            $arrOptions
        );
    }

    /**
     * Create a cache object for module on page (including current environment
     * @param   int
     * @param   int
     * @return  ProductCache
     */
    public static function createForPageAndModule($intPage, $intModule)
    {
        $objCache = new static();

        $objCache->setRow(array(
            'page_id'           => $intPage,
            'module_id'         => $intModule,
            'requestcache_id'   => (int) \Input::get('isorc'),
            'groups'            => static::getCacheableGroups(),
            'keywords'          => (string) \Input::get('keywords'),
        ));

        return $objCache;
    }

    /**
     * Delete cache for listing module, also delete expired ones while we're at it...
     * @param   int
     * @param   int
     */
    public static function deleteForPageAndModuleOrExpired($intPage, $intModule)
    {
        $time = time();

        \Database::getInstance()->prepare("
            DELETE FROM " . static::$strTable . "
            WHERE (page_id=? AND module_id=? AND requestcache_id=? AND groups=? AND keywords=?) OR (expires>0 AND expires<$time)
        ")->executeUncached($intPage, $intModule, (int) \Input::get('isorc'), static::getCacheableGroups(), (string) \Input::get('keywords'));
    }

    /**
     * Purge the product cache
     */
    public static function purge()
    {
        \Database::getInstance()->query("TRUNCATE " . static::$strTable);
    }

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

    /**
     * Check if cache is writable (table is not locked)
     * @return  bool
     */
    public static function isWritable()
    {
        return \Database::getInstance()->query("
            SHOW OPEN TABLES FROM `" . $GLOBALS['TL_CONFIG']['dbDatabase'] . "` LIKE '" . static::$strTable . "'
        ")->In_use == 0;
    }
}
