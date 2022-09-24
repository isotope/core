<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Database;
use Contao\FrontendUser;
use Contao\Input;
use Contao\Model;

/**
 * Isotope\Model\ProductCache represents an Isotope product cache model
 *
 * @property int    $id
 * @property int    $page_id
 * @property int    $module_id
 * @property int    $requestcache_id
 * @property string $keywords
 * @property array  $groups
 * @property array  $products
 * @property int    $expires
 */
class ProductCache extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_productcache';


    /**
     * Get array of product IDs
     *
     * @return array
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
     *
     * @param array $arrIds
     *
     * @return $this
     */
    public function setProductIds(array $arrIds)
    {
        $this->products = implode(',', array_map('intval', $arrIds));

        return $this;
    }

    /**
     * Find cache by unique ID (including current environment)
     *
     * @param string $uniqid     A 32 char unique key (usually MD5)
     * @param array  $arrOptions
     *
     * @return static
     */
    public static function findByUniqid($uniqid, array $arrOptions = array())
    {
        return static::findOneBy(
            array(
                'uniqid=?',
                "(keywords=? OR keywords='')",
                '(expires>? OR expires=0)',
                'tl_iso_productcache.groups=?'
            ),
            array(
                $uniqid,
                (string) Input::get('keywords'),
                time(),
                static::getCacheableGroups()
            ),
            $arrOptions
        );
    }

    /**
     * Create a cache object for a unique ID (including current environment
     *
     * @param string $uniqid A 32 char unique key (usually MD5)
     *
     * @return static
     */
    public static function createForUniqid($uniqid)
    {
        $objCache = new static();

        $objCache->setRow(
            array(
                  'uniqid'          => $uniqid,
                  'groups'          => static::getCacheableGroups(),
                  'keywords'        => (string) Input::get('keywords'),
              )
        );

        return $objCache;
    }

    /**
     * Delete cache for listing module, also delete expired ones while we're at it...
     *
     * @param string $uniqid A 32 char unique key (usually MD5)
     */
    public static function deleteByUniqidOrExpired($uniqid)
    {
        $time = time();

        Database::getInstance()->prepare("
            DELETE FROM tl_iso_productcache
            WHERE
                (uniqid=? AND tl_iso_productcache.groups=? AND (keywords='' OR keywords=?))
                OR (expires>0 AND expires<$time)
        ")->execute(
            $uniqid,
            (int) Input::get('isorc'),
            static::getCacheableGroups(),
            (string) Input::get('keywords')
        );
    }

    /**
     * Find cache for module on page (including current environment)
     *
     * @param int   $intPage
     * @param int   $intModule
     * @param array $arrOptions
     *
     * @return static
     *
     * @deprecated Deprecated since version 2.3, to be removed in 3.0.
     *             Use a findByUniqid() instead.
     */
    public static function findForPageAndModule($intPage, $intModule, array $arrOptions = array())
    {
        return static::findOneBy(
            array(
                 'page_id=?',
                 'module_id=?',
                 'requestcache_id=?',
                 "(keywords=? OR keywords='')",
                 '(expires>? OR expires=0)',
                 'groups=?'
            ),
            array(
                $intPage,
                $intModule,
                (int) Input::get('isorc'),
                (string) Input::get('keywords'),
                time(),
                static::getCacheableGroups()
            ),
            $arrOptions
        );
    }

    /**
     * Create a cache object for module on page (including current environment
     *
     * @param int $intPage
     * @param int $intModule
     *
     * @return static
     *
     * @deprecated Deprecated since version 2.3, to be removed in 3.0.
     *             Use createForUniqid() instead.
     */
    public static function createForPageAndModule($intPage, $intModule)
    {
        $objCache = new static();

        $objCache->setRow(array(
            'page_id'           => $intPage,
            'module_id'         => $intModule,
            'requestcache_id'   => (int) Input::get('isorc'),
            'groups'            => static::getCacheableGroups(),
            'keywords'          => (string) Input::get('keywords'),
        ));

        return $objCache;
    }

    /**
     * Delete cache for listing module, also delete expired ones while we're at it...
     *
     * @param int $intPage
     * @param int $intModule
     *
     * @deprecated Deprecated since version 2.3, to be removed in 3.0.
     *             Use deleteForUniqidOrExpired() instead.
     */
    public static function deleteForPageAndModuleOrExpired($intPage, $intModule)
    {
        $time = time();

        Database::getInstance()->prepare("
            DELETE FROM tl_iso_productcache
            WHERE
                (page_id=? AND module_id=? AND requestcache_id=? AND keywords=? AND tl_iso_productcache.groups=?)
                OR (expires>0 AND expires<$time)
        ")->execute(
            $intPage,
            $intModule,
            (int) Input::get('isorc'),
            (string) Input::get('keywords'),
            static::getCacheableGroups()
        );
    }

    /**
     * Purge the product cache
     */
    public static function purge()
    {
        Database::getInstance()->query("TRUNCATE " . static::$strTable);
    }

    /**
     * Return sorted and serialized list of active member groups for cache lookup
     *
     * @return string
     */
    public static function getCacheableGroups()
    {
        static $groups = null;

        if (null === $groups) {
            $groups = '';

            if (FE_USER_LOGGED_IN === true) {
                $user = FrontendUser::getInstance();
                $arrGroups = $user->groups;

                if (!empty($arrGroups) && \is_array($arrGroups)) {
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
     *
     * @return bool
     */
    public static function isWritable()
    {
        return Database::getInstance()->query('
            SHOW OPEN TABLES FROM `' . $GLOBALS['TL_CONFIG']['dbDatabase'] . "` LIKE '" . static::$strTable . "'
        ")->In_use == 0;
    }
}
