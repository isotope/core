<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Collection;

use Isotope\Interfaces\IsotopeProduct;
use Contao\Model\Collection;


/**
 * Class AttributeOption
 *
 * @method \Isotope\Model\AttributeOption[] getModels()
 * @method \Isotope\Model\AttributeOption current()
 */
class AttributeOption extends Collection
{

    /**
     * Get array structure suitable for a frontend widget
     *
     * @param IsotopeProduct $objProduct
     * @param bool           $blnPriceInLabel
     *
     * @return array
     */
    public function getArrayForFrontendWidget(IsotopeProduct $objProduct = null, $blnPriceInLabel = true)
    {
        $arrOptions = array();

        foreach ($this->getModels() as $objModel) {
            $arrOptions[] = $objModel->getAsArray($objProduct, $blnPriceInLabel);
        }

        return $arrOptions;
    }

    /**
     * Get array structure suitable for a backend widget
     *
     * @return array
     */
    public function getArrayForBackendWidget()
    {
        $group = null;
        $arrOptions = array();

        foreach ($this->getModels() as $objModel) {
            $option = $objModel->getAsArray();

            if ($option['group']) {
                $group = $option['label'];
                continue;
            }

            if (null !== $group) {
                $arrOptions[$group][] = $option;
            } else {
                $arrOptions[] = $option;
            }
        }

        return $arrOptions;
    }
}
