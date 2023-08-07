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

/**
 * Filters a product attribute using FIND_IN_SET().
 */
class CsvFilter extends Filter
{
    /**
     * Adds a filter to validate if attribute contains given value.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function contains($value)
    {
        $this->filter('FIND_IN_SET', $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sqlWhere()
    {
        return 'FIND_IN_SET(?, ' . $this->getFieldForSQL() . ')';
    }

    /**
     * @inheritdoc
     */
    public function getOperatorForSQL()
    {
        throw new \BadMethodCallException('The CsvFilter class cannot return an SQL operator.');
    }

    /**
     * @inheritdoc
     */
    protected function filter($operator, $value)
    {
        if ($operator !== 'FIND_IN_SET') {
            throw new \BadMethodCallException('The CsvFilter can only filter for values contained in the field.');
        }

        parent::filter($operator, $value);
    }
}
