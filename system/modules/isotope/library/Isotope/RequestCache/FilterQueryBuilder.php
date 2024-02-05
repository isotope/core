<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\RequestCache;

use Contao\Date;
use Isotope\Model\Product;
use Isotope\Model\ProductType;

class FilterQueryBuilder
{
    /**
     * @var Filter[]
     */
    private $filters;

    /**
     * @var string
     */
    private $sqlWhere = '';

    /**
     * @var array
     */
    private $sqlValues;

    /**
     * Constructor.
     *
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $filters)
    {
        array_walk($filters, function ($filter) {
            if (!$filter instanceof Filter) {
                throw new \InvalidArgumentException('Filters must be instances of \\Isotope\\RequestClass\\Filter');
            }
        });

        $this->buildSqlFilters($filters);
    }

    /**
     * Returns whether some of the filters can be executed on the database.
     *
     * @return bool
     */
    public function hasSqlCondition()
    {
        return '' !== $this->sqlWhere;
    }

    /**
     * Gets the filters.
     *
     * Potentially less than passed to the constructor,
     * because the ones applicable to the database are remove.
     *
     * @return Filter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Gets the SQL WHERE condition.
     *
     * @return string
     */
    public function getSqlWhere()
    {
        return $this->sqlWhere;
    }

    /**
     * Gets the SQL values for placeholders in the SQL condition (see FilterQueryBuilder::getSqlWhere()).
     *
     * @return array
     */
    public function getSqlValues()
    {
        return $this->sqlValues;
    }

    /**
     * Generate query string for native filters
     */
    private function buildSqlFilters(array $arrFilters)
    {
        $strWhere  = '';
        $arrWhere  = array();
        $arrValues = array();
        $arrGroups = array();

        // Initiate native SQL filtering
        /** @var \Isotope\RequestCache\Filter $objFilter  */
        foreach ($arrFilters as $k => $objFilter) {
            if ($objFilter->hasGroup() && ($arrGroups[$objFilter->getGroup()] ?? null) !== false) {
                if ($objFilter->isDynamicAttribute()) {
                    $arrGroups[$objFilter->getGroup()] = false;
                } else {
                    $arrGroups[$objFilter->getGroup()][] = $k;
                }
            } elseif (!$objFilter->hasGroup() && !$objFilter->isDynamicAttribute()) {
                $arrWhere[]  = $objFilter->sqlWhere();
                $arrValues = $this->addValue($arrValues, $objFilter->sqlValue());
                unset($arrFilters[$k]);
            }
        }

        if (0 !== \count($arrGroups)) {
            foreach ($arrGroups as $arrGroup) {
                $arrGroupWhere = array();

                // Skip dynamic attributes
                if (false === $arrGroup) {
                    continue;
                }

                foreach ($arrGroup as $k) {
                    $objFilter = $arrFilters[$k];

                    $arrGroupWhere[] = $objFilter->sqlWhere();
                    $arrValues = $this->addValue($arrValues, $objFilter->sqlValue());
                    unset($arrFilters[$k]);
                }

                $arrWhere[] = '(' . implode(' OR ', $arrGroupWhere) . ')';
            }
        }

        if (0 !== \count($arrWhere)) {
            $strWhere = implode(' AND ', $arrWhere);

            if (ProductType::countByVariants() > 0) {
                $time      = Date::floorToMinute();
                $t         = Product::getTable();
                $protected = '';

                if (BE_USER_LOGGED_IN !== true) {
                    $protected = "
                        AND $t.published='1'
                        AND ($t.start='' OR $t.start<'$time')
                        AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "')";
                }

                $strWhere = "
                    (
                        ($strWhere)
                        OR $t.id IN (
                            SELECT $t.pid
                            FROM tl_iso_product AS $t
                            WHERE $t.language='' AND " . implode(' AND ', $arrWhere) . "
                            $protected
                        )
                        OR $t.pid IN (
                            SELECT $t.id
                            FROM tl_iso_product AS $t
                            WHERE $t.language='' AND " . implode(' AND ', $arrWhere) . "
                            $protected
                        )
                    )
                ";

                $arrValues = array_merge($arrValues, $arrValues, $arrValues);
            }
        }

        $this->filters   = $arrFilters;
        $this->sqlWhere  = $strWhere;
        $this->sqlValues = $arrValues;
    }

    private function addValue(array $arrValues, $value)
    {
        if (\is_array($value)) {
            return array_merge($arrValues, $value);
        }

        $arrValues[] = $value;

        return $arrValues;
    }
}
