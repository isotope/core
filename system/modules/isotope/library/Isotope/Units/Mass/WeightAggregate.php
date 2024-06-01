<?php

/**
 * Haste utilities for Contao Open Source CMS
 *
 * Copyright (C) 2012-2013 Codefog & terminal42 gmbh
 *
 * @package    Haste
 * @link       http://github.com/codefog/contao-haste/
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Units\Mass;

interface WeightAggregate
{

    /**
     * Returns an object that implements the weightable interface
     *
     * @return Weighable
     */
    public function getWeight();
}
