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

namespace Isotope\RequestCache;

/**
 * Filters a product attribute using FIND_IN_SET().
 *
 * @author Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class CsvFilter extends Filter
{
    public function contains($value)
    {
        $this->filter('FIND_IN_SET', $value);

        return $this;
    }

    public function sqlWhere()
    {
        return 'FIND_IN_SET(?, ' . $this->getFieldForSQL() . ')';
    }

    public function getOperatorForSQL()
    {
        throw new \BadMethodCallException('The CsvFilter class cannot return an SQL operator.');
    }

    protected function filter($operator, $value)
    {
        if ($operator !== 'FIND_IN_SET') {
            throw new \BadMethodCallException('The CsvFilter can only filter for values contained in the field.');
        }

        parent::filter($operator, $value);
    }
}
