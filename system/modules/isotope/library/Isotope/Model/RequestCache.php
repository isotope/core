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
use Contao\Model;
use Isotope\RequestCache\Filter;
use Isotope\RequestCache\FilterQueryBuilder;
use Isotope\RequestCache\Limit;
use Isotope\RequestCache\Sort;
use Contao\Model\Registry;

/**
 * Isotope\Model\RequestCache represents an Isotope request cache model
 */
class RequestCache extends Model
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
     *
     * @return Filter[]
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

        return array_merge(...$arrMatches);
    }

    /**
     * Set filter config for a frontend module
     *
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

        return array_merge(...$arrMatches);
    }

    /**
     * Set sorting config for a frontend module
     *
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
        if (null === $this->arrSortings || !\is_array($this->arrSortings[$intModule] ?? null)) {
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
     * @param int  $intModule
     */
    public function addSortingForModule(Sort $objSort, $intModule)
    {
        if (null === $this->arrSortings || !\is_array($this->arrSortings[$intModule])) {
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
     * @param int    $intModule
     */
    public function setSortingForModule($strName, Sort $objSort, $intModule)
    {
        if (null === $this->arrSortings || !\is_array($this->arrSortings[$intModule] ?? null)) {
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
     * @param int $intDefault
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
        if ($this->isModified() && Registry::getInstance()->isRegistered($this)) {
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

        $arrSet = $this->preSave(['store_id' => (int) $this->store_id]);
        $objCache = static::findOneBy(['store_id=?', 'config_hash=?'], [$arrSet['store_id'], $arrSet['config_hash']]);

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
     *
     * @return Model
     */
    public function setRow(array $arrData)
    {
        // Do not use StringUtil::deserialize() because we have objects (see https://github.com/contao/core/issues/6695)
        /** @noinspection UnserializeExploitsInspection */
        $arrConfig = unserialize($arrData['config']);

        $this->arrFilters  = $arrConfig['filters'];
        $this->arrSortings = $arrConfig['sortings'];
        $this->arrLimits   = $arrConfig['limits'];

        return parent::setRow($arrData);
    }

    /**
     * Add object data to row
     *
     *
     * @return array
     */
    protected function preSave(array $arrSet)
    {
        $arrSet['config'] = array(
            'filters'   => empty($this->arrFilters) ? null : $this->arrFilters,
            'sortings'  => empty($this->arrSortings) ? null : $this->arrSortings,
            'limits'    => empty($this->arrLimits) ? null : $this->arrLimits
        );
        $arrSet['config_hash'] = md5(serialize($arrSet['config']));

        return $arrSet;
    }

    /**
     * Find cache by ID and store
     *
     * @param int   $intId
     * @param int   $intStore
     *
     * @return RequestCache|null
     */
    public static function findByIdAndStore($intId, $intStore, array $arrOptions = array())
    {
        if (null === $intId) {
            return null;
        }

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
        $affected = Database::getInstance()
            ->prepare("DELETE FROM tl_iso_requestcache WHERE id=?")
            ->execute($intId)
            ->affectedRows
        ;

        return ($affected > 0);
    }

    /**
     * Purge the request cache
     */
    public static function purge()
    {
        Database::getInstance()->query("TRUNCATE tl_iso_requestcache");
    }

    /**
     * Generate query string for native filters
     *
     *
     * @return array
     * @deprecated Deprecated since Isotope 2.3, to be removed in 3.0.
     *             Use Isotope\RequestCache\FilterQueryBuilder instead.
     */
    public static function buildSqlFilters(array $arrFilters)
    {
        $queryBuilder = new FilterQueryBuilder($arrFilters);

        return array($queryBuilder->getFilters(), $queryBuilder->getSqlWhere(), $queryBuilder->getSqlValues());
    }
}
