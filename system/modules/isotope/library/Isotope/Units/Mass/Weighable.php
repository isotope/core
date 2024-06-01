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

interface Weighable
{

    /**
     * Get the weight amount based on weight unit
     * @return  float
     */
    public function getWeightValue();

    /**
     * Get the weight unit
     * @return  string
     */
    public function getWeightUnit();

}
