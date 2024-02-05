<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;

/**
 * Attribute to implement additional image galleries
 */
class MediaManager extends Attribute
{
    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "blob NULL";

        // Media Manager must fetch fallback
        $arrData['fields'][$this->field_name]['attributes']['fetch_fallback'] = true;
    }

    /**
     * @inheritdoc
     *
     * @throws \BadMethodCallException because the MediaManager cannot be generated for frontend.
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        throw new \BadMethodCallException('MediaManager attribute cannot be generated');
    }
}
