<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
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
     * Filter configuration
     * @var array
     */
    protected $arrFilters;

    /**
     * Sorting configuration
     * @var array
     */
    protected $arrSortings;

    /**
     * Limit configuration
     * @var array
     */
    protected $arrLimits;


    /**
     * Check if request cache is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (null === $this->getFilters() && null === $this->getSortings() && null === $this->getLimits());
    }

    /**
     * Get filter configuration
     *
     * @return array|null
     */
    public function getFilters()
    {
        return $this->arrFilters;
    }

    /**
     * Get filter config for multiple modules
     *
     * @param array $arrIds
     *
     * @return array
     */
    public function getFiltersForModules(array $arrIds)
    {
        if ($this->arrFilters === null || empty($arrIds)) {
            return array();
        }

        $arrMatches = array_intersect_key($this->arrFilters, array_flip(array_reverse($arrIds)));

        if (empty($arrMatches)) {
            return array();
        }

        return call_user_func_array('array_merge', $arrMatches);
    }

    /**
     * Set filter config for a frontend module
     *
     * @param array $arrFilters
     * @param int   $intModule
     */
    public function setFiltersForModule(array $arrFilters, $intModule)
    {
        $this->arrFilters[$intModule] = $arrFilters;

        // Mark as modified
        $this->tstamp = time();
    }

    /**
     * Remove all filters for a frontend module
     *
     * @param int $intModule
     */
    public function unsetFiltersForModule($intModule)
    {
        if (isset($this->arrFilters[$intModule])) {
            unset($this->arrFilters[$intModule]);

            // Mark as modified
            $this->tstamp = time();
        }
    }

    /**
     * Return a specific filter by name and module
     *
     * @param string $strName
     * @param int    $intModule
     *
     * @return Filter|null
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
     *
     * @param Filter $objFilter
     * @param int    $intModule
     */
    public function addFilterForModule(Filter $objFilter, $intModule)
    {
        $this->arrFilters[$intModule][] = $objFilter;

        // Mark as modified
        $this->tstamp = time();
    }

    /**
     * Set filter by name for a frontend module
     *
     * @param string $strName
     * @param Filter $objFilter
     * @param int    $intModule
     */
    public function setFilterForModule($strName, Filter $objFilter, $intModule)
    {
        $this->arrFilters[$intModule][$strName] = $objFilter;

        // Mark as modified
        $this->tstamp = time();
    }

    /**
     * Remove a filter for a frontend module
     *
     * @param string $strName
     * @param int    $intModule
     */
    public function removeFilterForModule($strName, $intModule)
    {
        if (isset($this->arrFilters[$intModule]) || isset($this->arrFilters[$intModule][$strName])) {
            unset($this->arrFilters[$intModule][$strName]);

            if (empty($this->arrFilters[$intModule])) {
                unset($this->arrFilters[$intModule]);
            }

            // Mark as modified
            $this->tstamp = time();
        }
    }

    /**
     * Get sorting configuration
     *
     * @return  array|null
     */
    public function getSortings()
    {
        return $this->arrSortings;
    }

    /**
     * Get sorting configs for multiple modules
     *
     * @param array $arrIds
     *
     * @return array
     */
    public function getSortingsForModules(array $arrIds)
    {
        if (null === $this->arrSortings || empty($arrIds)) {
            return array();
        }

        $arrMatches = array_intersect_key($this->arrSortings, array_flip(array_reverse($arrIds)));

        if (empty($arrMatches)) {
            return array();
        }

        return call_user_func_array('array_merge', $arrMatches);
    }

    /**
     * Set sorting config for a frontend module
     *
     * @param array $arrSortings
     * @param int   $intModule
     */
    public function setSortingsForModule(array $arrSortings, $intModule)
    {
        $this->arrSortings[$intModule] = $arrSortings;

        // Mark as modified
        $this->tstamp = time();
    }

    /**
     * Remove sorting configs for a frontend module
     *
     * @param int $intModule
     */
    public function unsetSortingsForModule($intModule)
    {
        if (isset($this->arrSortings[$intModule])) {
            unset($this->arrSortings[$intModule]);

            // Mark as modified
            $this->tstamp = time();
        }
    }

    /**
     * Get first sorting field name for a frontend module
     *
     * @param int $intModule
     *
     * @return string
     */
    public function getFirstSortingFieldForModule($intModule)
    {
        if (null === $this->arrSortings || !is_array($this->arrSortings[$intModule])) {
            return '';
        }

        $arrNames = array_keys($this->arrSortings[$intModule]);

        return reset($arrNames);
    }

    /**
     * Return a specific sorting by name and module
     *
     * @param string $strName
     * @param int    $intModule
     *
     * @return Sort|null
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
     *
     * @param Sort $objSort
     * @param int  $intModule
     */
    public function addSortingForModule(Sort $objSort, $intModule)
    {
        if (null === $this->arrSortings || !is_array($this->arrSortings[$intModule])) {
            $this->arrSortings[$intModule] = array();
        }

        $this->arrSortings[$intModule] = array_merge(array($objSort), $this->arrSortings[$intModule]);

        // Mark as modified
        $this->tstamp = time();
    }

    /**
     * Set sorting by name for a frontend module
     *
     * @param string $strName
     * @param Sort   $objSort
     * @param int    $intModule
     */
    public function setSortingForModule($strName, Sort $objSort, $intModule)
    {
        if (null === $this->arrSortings || !is_array($this->arrSortings[$intModule])) {
            $this->arrSortings[$intModule] = array();
        }

        if (isset($this->arrSortings[$intModule][$strName])) {
            unset($this->arrSortings[$intModule][$strName]);
        }

        $this->arrSortings[$intModule] = array_merge(array($strName => $objSort), $this->arrSortings[$intModule]);

        // Mark as modified
        $this->tstamp = time();
    }

    /**
     * Remove a sorting for a frontend module
     *
     * @param string $strName
     * @param int    $intModule
     */
    public function removeSortingForModule($strName, $intModule)
    {
        if (isset($this->arrSortings[$intModule]) || isset($this->arrSortings[$intModule][$strName])) {
            unset($this->arrSortings[$intModule][$strName]);

            if (empty($this->arrSortings[$intModule])) {
                unset($this->arrSortings[$intModule]);
            }

            // Mark as modified
            $this->tstamp = time();
        }
    }

    /**
     * Get limit configuration
     *
     * @return array|null
     */
    public function getLimits()
    {
        return $this->arrLimits;
    }

    /**
     * Set limit for a frontend module
     *
     * @param Limit $objLimit
     * @param int   $intModule
     */
    public function setLimitForModule(Limit $objLimit, $intModule)
    {
        $this->arrLimits[$intModule] = $objLimit;

        // Mark as modified
        $this->tstamp = time();
    }

    /**
     * Return the first limit we can find
     *
     * @param array $arrIds
     * @param int $intDefault
     *
     * @return Limit
     */
    public function getFirstLimitForModules(array $arrIds, $intDefault = 0)
    {
        if (null !== $this->arrLimits) {
            foreach ($arrIds as $id) {
                if (isset($this->arrLimits[$id])) {
                    return $this->arrLimits[$id];
                }
            }
        }

        return Limit::to($intDefault);
    }

    /**
     * Do not allow to overwrite existing cache
     *
     * @return RequestCache
     * @throws \BadMethodCallException
     */
    public function save()
    {
        if ($this->isModified() && \Model\Registry::getInstance()->isRegistered($this)) {
            throw new \BadMethodCallException('Can\'t save a modified cache');
        }

        return parent::save();
    }

    /**
     * Return cache matching the current config, create or update if necessary
     *
     * @return RequestCache
     */
    public function saveNewConfiguration()
    {
        if (!$this->isModified()) {
            return $this;
        }

        $objCache = static::findOneBy(array('store_id=?', 'config=?'), $this->preSave(array((int) $this->store_id)));

        if (null === $objCache) {
            $objCache = clone $this;
        } elseif ($objCache->id == $this->id) {
            return $this;
        }

        return $objCache->save();
    }

    /**
     * @deprecated
     */
    public function saveNewConfiguartion()
    {
        return $this->saveNewConfiguration();
    }

    /**
     * Set the current record from an array
     *
     * @param array $arrData
     *
     * @return \Model
     */
    public function setRow(array $arrData)
    {
        // Do not use deserialize() because we have objects (see https://github.com/contao/core/issues/6695)
        $arrConfig = unserialize($arrData['config']);

        $this->arrFilters  = $arrConfig['filters'];
        $this->arrSortings = $arrConfig['sortings'];
        $this->arrLimits   = $arrConfig['limits'];

        return parent::setRow($arrData);
    }

    /**
     * Add object data to row
     *
     * @param array $arrSet
     *
     * @return array
     */
    protected function preSave(array $arrSet)
    {
        $arrSet['config'] = array(
            'filters'   => (empty($this->arrFilters) ? null : $this->arrFilters),
            'sortings'  => (empty($this->arrSortings) ? null : $this->arrSortings),
            'limits'    => (empty($this->arrLimits) ? null : $this->arrLimits)
        );

        return $arrSet;
    }

    /**
     * Find cache by ID and store
     *
     * @param int   $intId
     * @param int   $intStore
     * @param array $arrOptions
     *
     * @return RequestCache|null
     */
    public static function findByIdAndStore($intId, $intStore, array $arrOptions = array())
    {
        return static::findOneBy(array('id=?', 'store_id=?'), array($intId, $intStore), $arrOptions);
    }

    /**
     * Delete a cache by ID
     *
     * @param int $intId
     *
     * @return bool
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

    /**
     * Generate query string for native filters
     *
     * @param array $arrFilters
     *
     * @return array
     */
    public static function buildSqlFilters(array $arrFilters)
    {
        $strWhere  = '';
        $arrWhere  = array();
        $arrValues = array();
        $arrGroups = array();

        // Initiate native SQL filtering
        /** @var \Isotope\RequestCache\Filter $objFilter  */
        foreach ($arrFilters as $k => $objFilter) {
            if ($objFilter->hasGroup() && $arrGroups[$objFilter->getGroup()] !== false) {
                if ($objFilter->isDynamicAttribute()) {
                    $arrGroups[$objFilter->getGroup()] = false;
                } else {
                    $arrGroups[$objFilter->getGroup()][] = $k;
                }
            } elseif (!$objFilter->hasGroup() && !$objFilter->isDynamicAttribute()) {
                $arrWhere[]  = $objFilter->sqlWhere();
                $arrValues[] = $objFilter->sqlValue();
                unset($arrFilters[$k]);
            }
        }

        if (!empty($arrGroups)) {
            foreach ($arrGroups as $arrGroup) {
                $arrGroupWhere = array();

                foreach ($arrGroup as $k) {
                    $objFilter = $arrFilters[$k];

                    $arrGroupWhere[] = $objFilter->sqlWhere();
                    $arrValues[]     = $objFilter->sqlValue();
                    unset($arrFilters[$k]);
                }

                $arrWhere[] = '(' . implode(' OR ', $arrGroupWhere) . ')';
            }
        }

        if (!empty($arrWhere)) {
            $time = time();
            $t    = Product::getTable();

            $strWhere = "
                (
                    (" . implode(' AND ', $arrWhere) . ")
                    OR $t.id IN (SELECT $t.pid FROM tl_iso_product AS $t WHERE $t.language='' AND " . implode(' AND ', $arrWhere)
                . (BE_USER_LOGGED_IN === true ? '' : " AND $t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)") . ")
                    OR $t.pid IN (SELECT $t.id FROM tl_iso_product AS $t WHERE $t.language='' AND " . implode(' AND ', $arrWhere)
                . (BE_USER_LOGGED_IN === true ? '' : " AND $t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)") . ")
                )
            ";

            $arrValues = array_merge($arrValues, $arrValues, $arrValues);
        }

        return array($arrFilters, $strWhere, $arrValues);
    }
}