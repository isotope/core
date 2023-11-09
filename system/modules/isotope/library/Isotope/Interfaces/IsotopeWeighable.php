<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Interfaces;

interface IsotopeWeighable
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
