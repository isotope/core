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
 * Isotope\Model\RequestCache represents an Isotope request cache model
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class RequestCache extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_requestcache';

    /**
     * Filter configuration
     * @var array
     */
    protected $arrFilters = false;

    /**
     * Sorting configuration
     * @var array
     */
    protected $arrSorting = false;

    /**
     * Limit configuration
     * @var array
     */
    protected $arrLimit = false;


    /**
     * Get filter configuration
     * @return  array|null
     */
    public function getFilters()
    {
        if (false === $this->arrFilters) {
            $this->arrFilters = deserialize($this->filters);

            if (empty($this->arrFilters) || !is_array($this->arrFilters)) {
                $this->arrFilters = null;
            }
        }

        return $this->arrFilters;
    }

    /**
     * Get sorting configuration
     * @return  array|null
     */
    public function getSorting()
    {
        if (false === $this->arrSorting) {
            $this->arrSorting = deserialize($this->sorting);

            if (empty($this->arrSorting) || !is_array($this->arrSorting)) {
                $this->arrSorting = null;
            }
        }

        return $this->arrSorting;
    }

    /**
     * Get limit configuration
     * @return  array|null
     */
    public function getLimit()
    {
        if (false === $this->arrLimit) {
            $this->arrLimit = deserialize($this->limits);

            if (empty($this->arrLimit) || !is_array($this->arrLimit)) {
                $this->arrLimit = null;
            }
        }

        return $this->arrLimit;
    }

    /**
     * Return the first limit we can find
     * @param   array
     * @param   mixed
     * @return  int
     */
    public function getFirstLimitForModules(array $arrIds, $varDefault=0)
    {
        if (null === $this->getLimit()) {
            return $varDefault;
        }

        foreach ($arrIds as $id) {
            if (isset($this->arrLimit[$id])) {
                return $this->arrLimit[$id];
            }
        }

        return $varDefault;
    }

    /**
     * Find cache by ID and store
     * @param   int
     * @param   int
     * @return  RequestCache|null
     */
    public static function findByIdAndStore($intId, $intStore, array $arrOptions=array())
    {
        return static::findOneBy(array('id=?', 'store_id=?'), array($intId, $intStore), $arrOptions);
    }

    /**
     * Delete a cache by ID
     * @param   int
     */
    public static function deleteById($intId)
    {
        return (\Database::getInstance()->prepare("DELETE FROM " . static::$strTable . " WHERE id=?")->execute($intId)->affectedRows > 0);
    }

    /**
     * Purge the request cache
     */
    public static function purge()
    {
        \Database::getInstance()->query("TRUNCATE " . static::$strTable);
    }
}
