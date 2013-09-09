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

use Isotope\RequestCache\Filter;
use Isotope\RequestCache\Limit;
use Isotope\RequestCache\Sort;

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
     * Modified flag
     * @var bool
     */
    protected $blnModified = false;

    /**
     * Filter configuration
     * @var array
     */
    protected $arrFilters = false;

    /**
     * Sorting configuration
     * @var array
     */
    protected $arrSortings = false;

    /**
     * Limit configuration
     * @var array
     */
    protected $arrLimits = false;


    public function __clone()
    {
        parent::__clone();

        $this->blnModified = false;
    }


    /**
     * Check if request cache is empty
     * @return  bool
     */
    public function isEmpty()
    {
        return (null === $this->getFilters() && null === $this->getSortings() && null === $this->getLimits());
    }

    /**
     * Check if request chace is modified
     * @return  bool
     */
    public function isModified()
    {
        return $this->blnModified;
    }

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
     * Get filter config for multiple modules
     * @param   array
     * @return  array
     */
    public function getFiltersForModules(array $arrIds)
    {
        if ($this->getFilters() === null) {
            return array();
        }

        return call_user_func_array('array_merge', array_intersect_key($this->arrFilters, array_flip(array_reverse($arrIds))));
    }

    /**
     * Set filter config for a frontend module
     * @param   array
     * @param   int
     */
    public function setFiltersForModule(array $arrFilters, $intModule)
    {
        // Make sure filters are initialized and mark as modified
        $this->getFilters();
        $this->blnModified = true;

        $this->arrFilters[$intModule] = $arrFilters;
    }

    /**
     * Remove all filters for a frontend module
     */
    public function unsetFiltersForModule($intModule)
    {
        // Make sure filters are initialized and mark as modified
        $this->getFilters();
        $this->blnModified = true;

        unset($this->arrFilters[$intModule]);
    }

    /**
     * Return a specific filter by name and module
     * @param   string
     * @param   int
     * @return  Filter|null
     */
    public function getFilterForModule($strName, $intModule)
    {
        if (!isset($this->arrFilters[$intModule]) || !isset($this->arrFilters[$intModule][$strName])) {
            return null;
        }

        return $this->arrFilters[$intModule][$strName];
    }

    /**
     * Add an additional filter for a frontend module
     * @param   Filter
     * @param   int
     */
    public function addFilterForModule(Filter $objFilter, $intModule)
    {
        // Make sure filters are initialized and mark as modified
        $this->getFilters();
        $this->blnModified = true;

        $this->arrFilters[$intModule][] = $objFilter;
    }

    /**
     * Set filter by name for a frontend module
     * @param   string
     * @param   Filter
     * @param   int
     */
    public function setFilterForModule($strName, Filter $objFilter, $intModule)
    {
        // Make sure filters are initialized and mark as modified
        $this->getFilters();
        $this->blnModified = true;

        $this->arrFilters[$intModule][$strName] = $objFilter;
    }

    /**
     * Remove a filter for a frontend module
     * @param   string
     * @param   int
     */
    public function removeFilterForModule($strName, $intModule)
    {
        // Make sure filters are initialized and mark as modified
        $this->getFilters();

        if (isset($this->arrFilters[$intModule]) || isset($this->arrFilters[$intModule][$strName])) {
            $this->blnModified = true;

            unset($this->arrFilters[$intModule][$strName]);

            if (empty($this->arrFilters[$intModule])) {
                unset($this->arrFilters[$intModule]);
            }
        }
    }

    /**
     * Get sorting configuration
     * @return  array|null
     */
    public function getSortings()
    {
        if (false === $this->arrSortings) {
            $this->arrSortings = deserialize($this->sorting);

            if (empty($this->arrSortings) || !is_array($this->arrSortings)) {
                $this->arrSortings = null;
            }
        }

        return $this->arrSortings;
    }

    /**
     * Get sorting configs for multiple modules
     * @param   array
     * @return  array
     */
    public function getSortingsForModules(array $arrIds)
    {
        if (null === $this->getSortings()) {
            return array();
        }

        return call_user_func_array('array_merge', array_intersect_key($this->arrSortings, array_flip(array_reverse($arrIds))));
    }

    /**
     * Set sorting config for a frontend module
     * @param   array
     * @param   int
     */
    public function setSortingsForModule(array $arrSortings, $intModule)
    {
        // Make sure sorting is initialized and mark as modified
        $this->getSortings();
        $this->blnModified = true;

        $this->arrSortings[$intModule] = $arrSortings;
    }

    /**
     * Remove sorting configs for a frontend module
     */
    public function unsetSortingsForModule($intModule)
    {
        // Make sure filters are initialized and mark as modified
        $this->getFilters();
        $this->blnModified = true;

        unset($this->arrSortings[$intModule]);
    }

    /**
     * Return a specific sorting by name and module
     * @param   string
     * @param   int
     * @return  Sort|null
     */
    public function getSortingForModule($strName, $intModule)
    {
        if (!isset($this->arrSortings[$intModule]) || !isset($this->arrSortings[$intModule][$strName])) {
            return null;
        }

        return $this->arrSortings[$intModule][$strName];
    }

    /**
     * Add an additional sorting for a frontend module
     * @param   Sort
     * @param   int
     */
    public function addSortingForModule(Sort $objSort, $intModule)
    {
        // Make sure filters are initialized and mark as modified
        $this->getSortings();
        $this->blnModified = true;

        $this->arrSortings[$intModule][] = $objSort;
    }

    /**
     * Set sorting by name for a frontend module
     * @param   string
     * @param   Sort
     * @param   int
     */
    public function setSortingForModule($strName, Sort $objSort, $intModule)
    {
        // Make sure filters are initialized and mark as modified
        $this->getFilters();
        $this->blnModified = true;

        $this->arrFilters[$intModule][$strName] = $objSort;
    }

    /**
     * Remove a sorting for a frontend module
     * @param   string
     * @param   int
     */
    public function removeSortingForModule($strName, $intModule)
    {
        // Make sure sorting is initialized and mark as modified
        $this->getSortings();

        if (isset($this->arrSortings[$intModule]) || isset($this->arrSortings[$intModule][$strName])) {
            $this->blnModified = true;

            unset($this->arrSortings[$intModule][$strName]);

            if (empty($this->arrSortings[$intModule])) {
                unset($this->arrSortings[$intModule]);
            }
        }
    }

    /**
     * Get limit configuration
     * @return  array|null
     */
    public function getLimits()
    {
        if (false === $this->arrLimits) {
            $this->arrLimits = deserialize($this->limits);

            if (empty($this->arrLimits) || !is_array($this->arrLimits)) {
                $this->arrLimits = null;
            }
        }

        return $this->arrLimits;
    }

    /**
     * Set limit for a frontend module
     * @param   Limit
     * @param   int
     */
    public function setLimitForModule(Limit $objLimit, $intModule)
    {
        // Make sure sorting is initialized and mark as modified
        $this->getLimits();
        $this->blnModified = true;

        $this->arrLimits[$intModule] = $objLimit;
    }

    /**
     * Return the first limit we can find
     * @param   array
     * @param   int
     * @return  int
     */
    public function getFirstLimitForModules(array $arrIds, $intDefault=0)
    {
        if (null !== $this->getLimits()) {
            foreach ($arrIds as $id) {
                if (isset($this->arrLimits[$id])) {
                    return $this->arrLimits[$id];
                }
            }
        }

        return Limit::to($intDefault);
    }

    /**
     * Add object data to row
     * @param   array
     * @return  array
     */
    protected function preSave(array $arrSet)
    {
        // Store values in model and make sure they are re-initialized
        if ($this->blnModified) {
            $arrSet['filters'] = empty($this->arrFilters) ? null : $this->arrFilters;
            $arrSet['sorting'] = empty($this->arrSortings) ? null : $this->arrSortings;
            $arrSet['limits'] = empty($this->arrLimits) ? null : $this->arrLimits;

            $this->blnModified = false;
            $this->arrFilters = false;
            $this->arrSortings = false;
            $this->arrLimits = false;
        }

        return $arrSet;
    }

    /**
     * Do not allow to overwrite existing cache
     * @param   bool
     * @return  RequestCache
     * @throws  \BadMethodCallException
     */
    public function save($blnForceInsert=false)
    {
        if ($this->blnModified && !$blnForceInsert) {
            throw new \BadMethodCallException('Can\'t save a modified cache');
        }

        return parent::save($blnForceInsert);
    }

    /**
     * Return cache matching the current config, create or update if necessary
     * @return  RequestCache
     */
    public function saveNewConfiguartion()
    {
        if (!$this->blnModified) {
            return $this;
        }

        $arrColumns = array('store_id=?');
        $arrValues = array($this->store_id);

        if ($this->getFilters()) {
            $arrColumns[] = 'filters=?';
            $arrValues[] = serialize($this->getFilters());
        } else {
            $arrColumns[] = 'filters IS NULL';
        }

        if ($this->getSortings()) {
            $arrColumns[] = 'sorting=?';
            $arrValues[] = serialize($this->getSortings());
        } else {
            $arrColumns[] = 'sorting IS NULL';
        }

        if ($this->getLimits()) {
            $arrColumns[] = 'limits=?';
            $arrValues[] = serialize($this->getLimits());
        } else {
            $arrColumns[] = 'limits IS NULL';
        }

        $objCache = static::findOneBy($arrColumns, $arrValues);

        if (null === $objCache) {
            $objCache = clone $this;
        } elseif ($objCache->id == $this->id) {
            return $this;
        }

        $objCache->tstamp = time();

        return $objCache->save();
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
